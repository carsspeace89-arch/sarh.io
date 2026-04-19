<?php
// =============================================================
// src/Services/GeofenceService.php - Hardened Geofence with Risk Scoring
// =============================================================
// Combines GPS location, IP-based location, device consistency.
// Produces risk_score that flags potential tampering.
// =============================================================

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;

class GeofenceService
{
    private RedisCacheService $cache;

    // Risk score thresholds
    private const RISK_LOW = 0;
    private const RISK_MEDIUM = 30;
    private const RISK_HIGH = 60;
    private const RISK_BLOCK = 80;

    public function __construct(?RedisCacheService $cache = null)
    {
        $this->cache = $cache ?? RedisCacheService::getInstance();
    }

    /**
     * Haversine distance between two coordinates (meters)
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * Basic geofence check
     */
    public function isWithinGeofence(float $empLat, float $empLon, ?int $branchId = null): array
    {
        $workLat = 0.0;
        $workLon = 0.0;
        $radius = 500.0;

        if ($branchId) {
            $branch = $this->cache->getBranchConfig($branchId);
            if ($branch) {
                $workLat = (float)$branch['latitude'];
                $workLon = (float)$branch['longitude'];
                $radius = (float)$branch['geofence_radius'];
            }
        }

        if ($workLat == 0 && $workLon == 0) {
            $settings = $this->cache->getSettings();
            $workLat = (float)($settings['work_latitude'] ?? '0');
            $workLon = (float)($settings['work_longitude'] ?? '0');
            $radius = (float)($settings['geofence_radius'] ?? '500');
        }

        if ($workLat == 0 && $workLon == 0) {
            return ['allowed' => true, 'distance' => 0, 'radius' => 0, 'message' => 'موقع العمل غير محدد - مسموح'];
        }

        $distance = static::calculateDistance($empLat, $empLon, $workLat, $workLon);
        $allowed = $distance <= $radius;
        $dist = round($distance);

        return [
            'allowed' => $allowed,
            'distance' => $dist,
            'radius' => $radius,
            'message' => $allowed
                ? "أنت داخل نطاق العمل ({$dist} متر)"
                : "أنت خارج نطاق العمل! المسافة: {$dist} متر (الحد المسموح: {$radius} متر)",
        ];
    }

    /**
     * Full validation with risk scoring
     * Combines: GPS check + IP check + device consistency
     */
    public function validateWithRiskScore(
        int $employeeId,
        float $lat,
        float $lon,
        ?int $branchId,
        string $ip,
        ?string $deviceFingerprint = null,
        float $accuracy = 0
    ): array {
        $riskScore = self::RISK_LOW;
        $riskFactors = [];

        // 1. GPS Geofence check
        $geoCheck = $this->isWithinGeofence($lat, $lon, $branchId);
        if (!$geoCheck['allowed']) {
            $riskScore += 40;
            $riskFactors[] = 'outside_geofence';
        }

        // 2. GPS accuracy check (high accuracy = likely real GPS)
        if ($accuracy > 0 && $accuracy > 500) {
            $riskScore += 15;
            $riskFactors[] = 'low_gps_accuracy';
        }

        // 3. Impossible travel detection
        $travelRisk = $this->checkImpossibleTravel($employeeId, $lat, $lon);
        if ($travelRisk > 0) {
            $riskScore += $travelRisk;
            $riskFactors[] = 'impossible_travel';
        }

        // 4. Device consistency check (signal, not blocker)
        if ($deviceFingerprint !== null) {
            $deviceRisk = $this->checkDeviceConsistency($employeeId, $deviceFingerprint);
            $riskScore += $deviceRisk['score'];
            if (!empty($deviceRisk['factors'])) {
                $riskFactors = array_merge($riskFactors, $deviceRisk['factors']);
            }
        }

        // 5. IP-based approximate location check
        $ipRisk = $this->checkIPConsistency($ip, $lat, $lon, $branchId);
        if ($ipRisk > 0) {
            $riskScore += $ipRisk;
            $riskFactors[] = 'ip_location_mismatch';
        }

        // 6. Time-based anomaly check
        $timeRisk = $this->checkTimeAnomaly($employeeId);
        if ($timeRisk > 0) {
            $riskScore += $timeRisk;
            $riskFactors[] = 'time_anomaly';
        }

        $riskScore = min(100, $riskScore);

        // Determine action
        $blocked = false;
        $flagged = false;

        if ($riskScore >= self::RISK_BLOCK) {
            $blocked = true;
            $flagged = true;
            $this->flagTampering($employeeId, $riskScore, $riskFactors, $lat, $lon, $ip);
        } elseif ($riskScore >= self::RISK_HIGH) {
            $flagged = true;
            $this->flagTampering($employeeId, $riskScore, $riskFactors, $lat, $lon, $ip);
        } elseif ($riskScore >= self::RISK_MEDIUM) {
            $flagged = true;
            Logger::security('Medium risk attendance attempt', [
                'employee_id' => $employeeId,
                'risk_score' => $riskScore,
                'factors' => $riskFactors,
            ]);
        }

        return [
            'allowed' => $geoCheck['allowed'] && !$blocked,
            'distance' => $geoCheck['distance'],
            'radius' => $geoCheck['radius'],
            'risk_score' => $riskScore,
            'risk_level' => $this->getRiskLevel($riskScore),
            'risk_factors' => $riskFactors,
            'flagged' => $flagged,
            'blocked' => $blocked,
            'message' => $geoCheck['message'],
        ];
    }

    /**
     * Detect impossible travel (>5km in <5 minutes)
     */
    private function checkImpossibleTravel(int $employeeId, float $lat, float $lon): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT latitude, longitude, timestamp FROM attendances
            WHERE employee_id = ? AND attendance_date = CURDATE()
              AND latitude IS NOT NULL AND latitude != 0
            ORDER BY timestamp DESC LIMIT 1
        ");
        $stmt->execute([$employeeId]);
        $last = $stmt->fetch();

        if (!$last || !$last['latitude']) {
            return 0;
        }

        $distance = static::calculateDistance($lat, $lon, (float)$last['latitude'], (float)$last['longitude']);
        $timeDiff = time() - strtotime($last['timestamp']);

        // >5km in <5 minutes = impossible (max driving speed ~100km/h = ~1.67km/min)
        if ($timeDiff > 0 && $timeDiff < 300 && $distance > 5000) {
            Logger::security('Impossible travel detected', [
                'employee_id' => $employeeId,
                'distance_m' => round($distance),
                'time_sec' => $timeDiff,
            ]);
            return 35;
        }

        // >2km in <2 minutes
        if ($timeDiff > 0 && $timeDiff < 120 && $distance > 2000) {
            return 20;
        }

        return 0;
    }

    /**
     * Check device fingerprint consistency
     * Treat fingerprint as a SIGNAL, not as security.
     */
    private function checkDeviceConsistency(int $employeeId, string $fingerprint): array
    {
        $db = Database::getInstance();
        $score = 0;
        $factors = [];

        // Get known devices for this employee
        $stmt = $db->prepare("
            SELECT DISTINCT user_agent FROM attendances
            WHERE employee_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              AND user_agent IS NOT NULL AND user_agent != ''
            ORDER BY timestamp DESC LIMIT 5
        ");
        $stmt->execute([$employeeId]);
        $knownUAs = array_column($stmt->fetchAll(), 'user_agent');

        // Check if this device has been used by OTHER employees today
        $stmt = $db->prepare("
            SELECT DISTINCT employee_id FROM attendances
            WHERE attendance_date = CURDATE()
              AND user_agent = ?
              AND employee_id != ?
            LIMIT 3
        ");
        $stmt->execute([$_SERVER['HTTP_USER_AGENT'] ?? '', $employeeId]);
        $sharedDevice = $stmt->fetchAll();

        if (count($sharedDevice) > 0) {
            $score += 15;
            $factors[] = 'shared_device';
        }
        if (count($sharedDevice) > 2) {
            $score += 20;
            $factors[] = 'multi_shared_device';
        }

        // New device detection (informational, not blocking)
        $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!empty($knownUAs) && !in_array($currentUA, $knownUAs, true)) {
            $score += 5;
            $factors[] = 'new_device';
        }

        return ['score' => $score, 'factors' => $factors];
    }

    /**
     * IP-based consistency check
     * Approximate check: if IP country/region is wildly different from branch location
     */
    private function checkIPConsistency(string $ip, float $lat, float $lon, ?int $branchId): int
    {
        // Check if IP is from a known VPN/proxy range (basic heuristic)
        if ($this->isPrivateIP($ip)) {
            return 0; // Local network, no risk
        }

        // Check if this IP was recently used by many different employees (suspicious)
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT employee_id) as emp_count FROM attendances
            WHERE ip_address = ? AND attendance_date = CURDATE()
        ");
        $stmt->execute([$ip]);
        $count = (int)$stmt->fetchColumn();

        // More than 10 different employees from same IP in one day is unusual
        // (unless they're all at the same office behind NAT, which is normal)
        if ($count > 20) {
            return 10;
        }

        return 0;
    }

    /**
     * Time-based anomaly check
     */
    private function checkTimeAnomaly(int $employeeId): int
    {
        // Check if employee has already checked in/out too many times today
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM attendances
            WHERE employee_id = ? AND attendance_date = CURDATE()
        ");
        $stmt->execute([$employeeId]);
        $todayCount = (int)$stmt->fetchColumn();

        // More than 8 attendance records in a day is suspicious
        if ($todayCount > 8) {
            return 15;
        }

        return 0;
    }

    /**
     * Flag tampering in database
     */
    private function flagTampering(int $employeeId, int $riskScore, array $factors, float $lat, float $lon, string $ip): void
    {
        $db = Database::getInstance();
        try {
            $db->prepare("
                INSERT INTO tampering_log (employee_id, case_type, description, latitude, longitude, evidence_json, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ")->execute([
                $employeeId,
                'risk_score_' . $this->getRiskLevel($riskScore),
                'Risk score: ' . $riskScore . ' | Factors: ' . implode(', ', $factors),
                $lat,
                $lon,
                json_encode([
                    'risk_score' => $riskScore,
                    'factors' => $factors,
                    'ip' => $ip,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                ], JSON_UNESCAPED_UNICODE),
            ]);

            // Create notification for admin
            $notifService = new NotificationService();
            $notifService->notifyTampering($employeeId, "Employee #{$employeeId}", implode(', ', $factors));
        } catch (\Throwable $e) {
            Logger::error('Failed to flag tampering', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map risk score to risk level string
     */
    private function getRiskLevel(int $score): string
    {
        if ($score >= self::RISK_BLOCK) return 'critical';
        if ($score >= self::RISK_HIGH) return 'high';
        if ($score >= self::RISK_MEDIUM) return 'medium';
        return 'low';
    }

    /**
     * Check if IP is private/local
     */
    private function isPrivateIP(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
