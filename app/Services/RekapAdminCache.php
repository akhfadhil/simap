<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RekapAdminCache
{
    private const AGGREGATE_KEYS = 'rekap_admin_aggregate_keys';
    private const CHART_KEYS = 'rekap_admin_chart_keys';

    public static function rememberAggregate(string $jenis, ?int $dapilId, callable $callback): array
    {
        $key = self::aggregateKey($jenis, $dapilId);
        $keys = Cache::get(self::AGGREGATE_KEYS, []);

        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::forever(self::AGGREGATE_KEYS, $keys);
        }

        return Cache::remember($key, now()->addMinutes(10), $callback);
    }

    public static function rememberChart(array $parts, callable $callback): array
    {
        $key = 'rekap_admin_chart_' . md5(json_encode($parts));
        $keys = Cache::get(self::CHART_KEYS, []);

        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::forever(self::CHART_KEYS, $keys);
        }

        return Cache::remember($key, now()->addMinutes(10), $callback);
    }

    public static function flushAggregate(): void
    {
        foreach (Cache::get(self::AGGREGATE_KEYS, []) as $key) {
            Cache::forget($key);
        }

        foreach (Cache::get(self::CHART_KEYS, []) as $key) {
            Cache::forget($key);
        }

        Cache::forget(self::AGGREGATE_KEYS);
        Cache::forget(self::CHART_KEYS);
    }

    private static function aggregateKey(string $jenis, ?int $dapilId): string
    {
        return 'rekap_admin_aggregate_' . $jenis . '_' . ($dapilId ?: 'all');
    }
}
