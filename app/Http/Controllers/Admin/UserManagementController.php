<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Tps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('kecamatan', 'desa', 'tps')
                    ->where('role', '!=', 'admin')
                    ->when(request('role'), fn($q) => $q->where('role', request('role')))
                    ->latest()
                    ->paginate(15)
                    ->withQueryString();

        $kecamatans = Kecamatan::all();
        $desas      = Desa::with('kecamatan')->get();
        $tpsList    = Tps::with('desa')->get();

        return view('admin.users.index', compact('users', 'kecamatans', 'desas', 'tpsList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'username'     => 'required|string|unique:users|max:50',
            'password'     => 'required|string|min:6',
            'role'         => 'required|in:ppk,pps,kpps',
            'kecamatan_id' => 'required_if:role,ppk|nullable|exists:kecamatans,id',
            'desa_id'      => 'required_if:role,pps|nullable|exists:desas,id',
            'tps_id'       => 'required_if:role,kpps|nullable|exists:tps,id',
        ]);

        User::create([
            'name'         => $request->name,
            'username'     => $request->username,
            'email'        => $request->username . '@pemilu.id',
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'kecamatan_id' => $request->role === 'ppk'  ? $request->kecamatan_id : null,
            'desa_id'      => $request->role === 'pps'  ? $request->desa_id      : null,
            'tps_id'       => $request->role === 'kpps' ? $request->tps_id       : null,
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:users,username,' . $user->id,
            'password'     => 'nullable|string|min:6',
            'kecamatan_id' => 'nullable|exists:kecamatans,id',
            'desa_id'      => 'nullable|exists:desas,id',
            'tps_id'       => 'nullable|exists:tps,id',
        ]);

        $data = [
            'name'         => $request->name,
            'username'     => $request->username,
            'kecamatan_id' => $user->role === 'ppk'  ? $request->kecamatan_id : null,
            'desa_id'      => $user->role === 'pps'  ? $request->desa_id      : null,
            'tps_id'       => $user->role === 'kpps' ? $request->tps_id       : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return back()->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}