<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public const ALLOW_SUPPORTING_DEPT = 'reporting.allow_supporting_department_reporting';

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return $this->cast($setting->value, $setting->type);
    }

    public function set(string $key, mixed $value): Setting
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $type = $setting->type ?: $this->inferType($value);
        $setting->type = $type;
        $setting->value = $this->serialize($value, $type);
        $setting->save();

        return $setting;
    }

    public function allPublic(): array
    {
        return Setting::all()
            ->mapWithKeys(fn (Setting $s) => [$s->key => $this->cast($s->value, $s->type)])
            ->all();
    }

    private function cast(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $value, true),
            default => $value,
        };
    }

    private function serialize(mixed $value, string $type): ?string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => $value === null ? null : (string) $value,
        };
    }

    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_array($value) => 'json',
            default => 'string',
        };
    }
}
