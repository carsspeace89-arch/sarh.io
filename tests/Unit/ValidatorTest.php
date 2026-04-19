<?php
// =============================================================
// tests/Unit/ValidatorTest.php - اختبارات طبقة التحقق
// =============================================================

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Validator;

class ValidatorTest extends TestCase
{
    // ==================== required ====================

    public function testRequiredPassesWithValue(): void
    {
        $v = Validator::make(['name' => 'أحمد'])->required('name');
        $this->assertTrue($v->passes());
    }

    public function testRequiredFailsWhenMissing(): void
    {
        $v = Validator::make([])->required('name');
        $this->assertTrue($v->fails());
    }

    public function testRequiredFailsWhenEmpty(): void
    {
        $v = Validator::make(['name' => ''])->required('name');
        $this->assertTrue($v->fails());
    }

    public function testRequiredFailsWhenWhitespace(): void
    {
        $v = Validator::make(['name' => '   '])->required('name');
        $this->assertTrue($v->fails());
    }

    // ==================== string ====================

    public function testStringPassesValidLength(): void
    {
        $v = Validator::make(['name' => 'أحمد'])->string('name', 1, 50);
        $this->assertTrue($v->passes());
    }

    public function testStringFailsTooShort(): void
    {
        $v = Validator::make(['name' => 'a'])->string('name', 5, 50);
        $this->assertTrue($v->fails());
    }

    public function testStringFailsTooLong(): void
    {
        $v = Validator::make(['name' => str_repeat('x', 300)])->string('name', 1, 255);
        $this->assertTrue($v->fails());
    }

    public function testStringSkipsNull(): void
    {
        $v = Validator::make([])->string('name', 1, 50);
        $this->assertTrue($v->passes());
    }

    // ==================== numeric ====================

    public function testNumericPasses(): void
    {
        $v = Validator::make(['lat' => '24.7136'])->numeric('lat', -90, 90);
        $this->assertTrue($v->passes());
    }

    public function testNumericFailsNonNumeric(): void
    {
        $v = Validator::make(['lat' => 'abc'])->numeric('lat');
        $this->assertTrue($v->fails());
    }

    public function testNumericFailsBelowMin(): void
    {
        $v = Validator::make(['lat' => '-100'])->numeric('lat', -90, 90);
        $this->assertTrue($v->fails());
    }

    public function testNumericFailsAboveMax(): void
    {
        $v = Validator::make(['lat' => '100'])->numeric('lat', -90, 90);
        $this->assertTrue($v->fails());
    }

    // ==================== PIN ====================

    public function testPinPassesValid(): void
    {
        $v = Validator::make(['pin' => '1234'])->pin('pin');
        $this->assertTrue($v->passes());
    }

    public function testPinFailsTooShort(): void
    {
        $v = Validator::make(['pin' => '123'])->pin('pin');
        $this->assertTrue($v->fails());
    }

    public function testPinFailsNonNumeric(): void
    {
        $v = Validator::make(['pin' => 'abcd'])->pin('pin');
        $this->assertTrue($v->fails());
    }

    // ==================== latitude/longitude ====================

    public function testLatitudeValid(): void
    {
        $v = Validator::make(['lat' => '24.7136'])->latitude('lat');
        $this->assertTrue($v->passes());
    }

    public function testLatitudeInvalid(): void
    {
        $v = Validator::make(['lat' => '91'])->latitude('lat');
        $this->assertTrue($v->fails());
    }

    public function testLongitudeValid(): void
    {
        $v = Validator::make(['lon' => '46.6753'])->longitude('lon');
        $this->assertTrue($v->passes());
    }

    public function testLongitudeInvalid(): void
    {
        $v = Validator::make(['lon' => '200'])->longitude('lon');
        $this->assertTrue($v->fails());
    }

    // ==================== date ====================

    public function testDateValid(): void
    {
        $v = Validator::make(['date' => '2025-01-15'])->date('date');
        $this->assertTrue($v->passes());
    }

    public function testDateInvalid(): void
    {
        $v = Validator::make(['date' => '2025-13-40'])->date('date');
        $this->assertTrue($v->fails());
    }

    // ==================== time ====================

    public function testTimeValid(): void
    {
        $v = Validator::make(['time' => '08:30'])->time('time');
        $this->assertTrue($v->passes());
    }

    public function testTimeInvalid(): void
    {
        $v = Validator::make(['time' => '25:00'])->time('time');
        $this->assertTrue($v->fails());
    }

    // ==================== in ====================

    public function testInValid(): void
    {
        $v = Validator::make(['type' => 'in'])->in('type', ['in', 'out']);
        $this->assertTrue($v->passes());
    }

    public function testInInvalid(): void
    {
        $v = Validator::make(['type' => 'break'])->in('type', ['in', 'out']);
        $this->assertTrue($v->fails());
    }

    // ==================== email ====================

    public function testEmailValid(): void
    {
        $v = Validator::make(['email' => 'test@example.com'])->email('email');
        $this->assertTrue($v->passes());
    }

    public function testEmailInvalid(): void
    {
        $v = Validator::make(['email' => 'not-email'])->email('email');
        $this->assertTrue($v->fails());
    }

    // ==================== combined validation ====================

    public function testCombinedValidation(): void
    {
        $v = Validator::make([
            'token' => 'abc123',
            'latitude' => '24.7136',
            'longitude' => '46.6753',
        ])
            ->required('token')
            ->string('token', 1, 255)
            ->required('latitude')
            ->latitude('latitude')
            ->required('longitude')
            ->longitude('longitude');

        $this->assertTrue($v->passes());
    }

    public function testCombinedMultipleErrors(): void
    {
        $v = Validator::make([])
            ->required('token', 'الرمز')
            ->required('latitude', 'خط العرض');

        $this->assertTrue($v->fails());
        $errors = $v->errors();
        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('token', $errors);
        $this->assertArrayHasKey('latitude', $errors);
    }

    // ==================== firstError ====================

    public function testFirstErrorReturnsFirst(): void
    {
        $v = Validator::make([])->required('a', 'حقل A')->required('b', 'حقل B');
        $this->assertNotNull($v->firstError());
        $this->assertStringContainsString('حقل A', $v->firstError());
    }

    public function testFirstErrorReturnsNullWhenValid(): void
    {
        $v = Validator::make(['a' => 'val'])->required('a');
        $this->assertNull($v->firstError());
    }

    // ==================== validated data ====================

    public function testValidatedField(): void
    {
        $v = Validator::make(['name' => 'أحمد', 'age' => '30']);
        $this->assertEquals('أحمد', $v->validated('name'));
        $this->assertEquals('default', $v->validated('missing', 'default'));
    }

    public function testAllValidated(): void
    {
        $data = ['name' => 'أحمد', 'age' => '30', 'extra' => 'ignored'];
        $v = Validator::make($data);
        $result = $v->allValidated(['name', 'age']);
        $this->assertEquals(['name' => 'أحمد', 'age' => '30'], $result);
        $this->assertArrayNotHasKey('extra', $result);
    }
}
