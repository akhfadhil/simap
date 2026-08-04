<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'username' => $request->user()->username,
                    'role' => $request->user()->role,
                    'kecamatan_id' => $request->user()->kecamatan_id,
                    'desa_id' => $request->user()->desa_id,
                    'tps_id' => $request->user()->tps_id,
                    'partai_id' => $request->user()->partai_id,
                    'partai' => $request->user()->partai,
                    'kecamatan' => $request->user()->kecamatan,
                    'desa' => $request->user()->desa,
                    'tps' => $request->user()->tps,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'adminViewSession' => [
                'kecamatan_id' => fn () => session('admin_view_kecamatan_id'),
                'desa_id' => fn () => session('admin_view_desa_id'),
                'tps_id' => fn () => session('admin_view_tps_id'),
            ],
        ]);
    }
}
