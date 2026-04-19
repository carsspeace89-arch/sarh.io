<?php
// =============================================================
// src/Core/Validator.php - طبقة التحقق المركزية
// =============================================================

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * إنشاء مثيل جديد
     */
    public static function make(array $data): self
    {
        return new self($data);
    }

    /**
     * التحقق من حقل مطلوب
     */
    public function required(string $field, ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || (is_string($value) && trim($value) === '')) {
            $this->errors[$field][] = ($label ?? $field) . ' مطلوب';
        }
        return $this;
    }

    /**
     * التحقق من نوع نصي وطول
     */
    public function string(string $field, int $min = 0, int $max = 255, ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        if (!is_string($value)) {
            $this->errors[$field][] = ($label ?? $field) . ' يجب أن يكون نصاً';
            return $this;
        }

        $len = mb_strlen($value);
        if ($min > 0 && $len < $min) {
            $this->errors[$field][] = ($label ?? $field) . " يجب أن يكون على الأقل {$min} أحرف";
        }
        if ($len > $max) {
            $this->errors[$field][] = ($label ?? $field) . " يجب ألا يتجاوز {$max} حرف";
        }
        return $this;
    }

    /**
     * التحقق من رقم
     */
    public function numeric(string $field, ?float $min = null, ?float $max = null, ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        if (!is_numeric($value)) {
            $this->errors[$field][] = ($label ?? $field) . ' يجب أن يكون رقماً';
            return $this;
        }

        $num = (float)$value;
        if ($min !== null && $num < $min) {
            $this->errors[$field][] = ($label ?? $field) . " يجب أن يكون على الأقل {$min}";
        }
        if ($max !== null && $num > $max) {
            $this->errors[$field][] = ($label ?? $field) . " يجب ألا يتجاوز {$max}";
        }
        return $this;
    }

    /**
     * التحقق من عدد صحيح
     */
    public function integer(string $field, ?int $min = null, ?int $max = null, ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        if (!is_numeric($value) || (int)$value != $value) {
            $this->errors[$field][] = ($label ?? $field) . ' يجب أن يكون عدداً صحيحاً';
            return $this;
        }

        return $this->numeric($field, $min, $max, $label);
    }

    /**
     * التحقق من بريد إلكتروني
     */
    public function email(string $field, ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = ($label ?? $field) . ' بريد إلكتروني غير صالح';
        }
        return $this;
    }

    /**
     * التحقق من تاريخ
     */
    public function date(string $field, string $format = 'Y-m-d', ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        $d = \DateTime::createFromFormat($format, $value);
        if (!$d || $d->format($format) !== $value) {
            $this->errors[$field][] = ($label ?? $field) . " تاريخ غير صالح (الصيغة: {$format})";
        }
        return $this;
    }

    /**
     * التحقق من وقت
     */
    public function time(string $field, ?string $label = null): self
    {
        return $this->date($field, 'H:i', $label);
    }

    /**
     * التحقق من أن القيمة ضمن قائمة مسموحة
     */
    public function in(string $field, array $allowed, ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        if (!in_array($value, $allowed, true)) {
            $this->errors[$field][] = ($label ?? $field) . ' قيمة غير مسموحة';
        }
        return $this;
    }

    /**
     * التحقق بتعبير نمطي
     */
    public function regex(string $field, string $pattern, ?string $message = null, ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        if (!preg_match($pattern, $value)) {
            $this->errors[$field][] = $message ?? (($label ?? $field) . ' صيغة غير صالحة');
        }
        return $this;
    }

    /**
     * التحقق من PIN (4 أرقام)
     */
    public function pin(string $field, ?string $label = null): self
    {
        return $this->regex($field, '/^\d{4}$/', ($label ?? $field) . ' يجب أن يكون 4 أرقام');
    }

    /**
     * التحقق من إحداثيات GPS
     */
    public function latitude(string $field, ?string $label = null): self
    {
        return $this->numeric($field, -90, 90, $label ?? 'خط العرض');
    }

    public function longitude(string $field, ?string $label = null): self
    {
        return $this->numeric($field, -180, 180, $label ?? 'خط الطول');
    }

    /**
     * التحقق من token (hex string)
     */
    public function token(string $field, int $minLength = 16, ?string $label = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null) return $this;

        if (!preg_match('/^[a-fA-F0-9]{' . $minLength . ',}$/', $value)) {
            $this->errors[$field][] = ($label ?? $field) . ' رمز غير صالح';
        }
        return $this;
    }

    /**
     * هل التحقق ناجح؟
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * هل التحقق فاشل؟
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * الأخطاء
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * أول خطأ فقط (للـ API)
     */
    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }

    /**
     * القيمة المُنظّفة
     */
    public function validated(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    /**
     * كل القيم المُنظّفة
     */
    public function allValidated(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $this->data)) {
                $result[$field] = $this->data[$field];
            }
        }
        return $result;
    }
}
