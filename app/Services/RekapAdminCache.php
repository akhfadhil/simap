<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RekapAdminCache
{
    private const AGGREGATE_KEYS = 'rekap_admin_aggregate_keys';

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

    public static function flushAggregate(): void
    {
        foreach (Cache::get(self::AGGREGATE_KEYS, []) as $key) {
            Cache::forget($key);
        }

        Cache::forget(self::AGGREGATE_KEYS);
    }

    private static function aggregateKey(string $jenis, ?int $dapilId): string
    {
        return 'rekap_admin_aggregate_' . $jenis . '_' . ($dapilId ?: 'all');
    }
}
