<?php
// =============================================================
// src/Controllers/Api/V1/AuthController.php - Authentication API Controller
// =============================================================

namespace App\Controllers\Api\V1;

use App\Core\Controller;
use App\Core\Logger;
use App\Services\AuthService;
use App\Middleware\RedisRateLimiter;

class AuthController extends Controller
{
    private AuthService $authService;
    private RedisRateLimiter $rateLimiter;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->rateLimiter = new RedisRateLimiter();
    }

    /**
     * POST /api/v1/auth/pin
     */
    public function authenticateByPin(): void
    {
        $limit = $this->rateLimiter->checkByIP('auth_pin', 10, 60);
        if (!$limit['allowed']) {
            $this->rateLimiter->denyResponse($limit['retry_after']);
        }

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $body = $this->jsonBody();
        $v = $this->validate($body)
            ->required('pin', 'رمز PIN')
            ->pin('pin', 'رمز PIN');

        if ($v->fails()) {
            $this->validationError($v);
        }

        $pin = trim($body['pin']);

        $employee = (new \App\Models\Employee())->findByPin($pin);
        if (!$employee) {
            Logger::security('Failed PIN auth attempt', ['pin' => '****', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            $this->json(['success' => false, 'message' => 'رمز PIN غير صالح أو الموظف غير مفعّل'], 403);
        }

        Logger::api('PIN auth success', ['employee_id' => $employee['id']]);

        $this->json([
            'success' => true,
            'message' => 'تم التحقق بنجاح',
            'employee' => [
                'id' => $employee['id'],
                'name' => $employee['name'],
                'token' => $employee['unique_token'],
                'branch_id' => $employee['branch_id'],
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/device
     */
    public function authenticateByDevice(): void
    {
        $limit = $this->rateLimiter->checkByIP('auth_device', 20, 60);
        if (!$limit['allowed']) {
            $this->rateLimiter->denyResponse($limit['retry_after']);
        }

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $body = $this->jsonBody();
        $v = $this->validate($body)
            ->required('fingerprint', 'بصمة الجهاز')
            ->string('fingerprint', 32, 512, 'بصمة الجهاز');

        if ($v->fails()) {
            $this->validationError($v);
        }

        $fingerprint = trim($body['fingerprint']);

        $employee = (new \App\Models\Employee())->findByDeviceFingerprint($fingerprint);

        if (!$employee) {
            Logger::security('Failed device auth attempt', ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            $this->json(['success' => false, 'message' => 'الجهاز غير مسجل لأي موظف'], 403);
        }

        Logger::api('Device auth success', ['employee_id' => $employee['id']]);

        $this->json([
            'success' => true,
            'message' => 'تم التحقق بنجاح',
            'employee' => [
                'id' => $employee['id'],
                'name' => $employee['name'],
                'token' => $employee['unique_token'],
                'branch_id' => $employee['branch_id'],
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(): void
    {
        $limit = $this->rateLimiter->checkByIP('admin_login', 5, 300);
        if (!$limit['allowed']) {
            $this->rateLimiter->denyResponse($limit['retry_after']);
        }

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $body = $this->jsonBody();
        $v = $this->validate($body)
            ->required('username', 'اسم المستخدم')
            ->string('username', 1, 100, 'اسم المستخدم')
            ->required('password', 'كلمة المرور')
            ->string('password', 1, 255, 'كلمة المرور');

        if ($v->fails()) {
            $this->validationError($v);
        }

        $username = trim($body['username']);
        $password = $body['password'];
        $remember = (bool)($body['remember'] ?? false);

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!$this->authService->checkLoginAttempts($ip)) {
            Logger::security('Login rate limit exceeded', ['ip' => $ip, 'username' => $username]);
            $this->json(['success' => false, 'message' => 'تم تجاوز عدد المحاولات المسموح. حاول لاحقاً.'], 429);
        }

        $result = $this->authService->login($username, $password, $remember);

        if (!$result['success']) {
            $this->authService->recordFailedAttempt($ip, $username);
            Logger::security('Failed admin login', ['ip' => $ip, 'username' => $username]);
        } else {
            Logger::info('Admin login success', ['username' => $username]);
        }

        $this->json($result, $result['success'] ? 200 : 401);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->json(['success' => true, 'message' => 'تم تسجيل الخروج']);
    }
}
