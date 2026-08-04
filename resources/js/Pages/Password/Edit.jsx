import React from 'react';
import { useForm, Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { Lock, Save, KeyRound } from 'lucide-react';

export default function Edit() {
    const { data, setData, post, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/password', {
            onSuccess: () => reset('current_password', 'password', 'password_confirmation'),
        });
    };

    return (
        <AdminLayout title="Ubah Password">
            <Head title="Ubah Password" />

            <div className="max-w-xl mx-auto">
                <div className="mb-6">
                    <p className="text-[10px] tracking-widest text-slate-400 dark:text-slate-500 uppercase font-semibold">// Akun Pengguna</p>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1">Ubah Password</h1>
                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">Perbarui kata sandi akun Anda demi keamanan data.</p>
                </div>

                <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Password Lama
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <KeyRound className="w-4 h-4" />
                                </div>
                                <input
                                    type="password"
                                    value={data.current_password}
                                    onChange={(e) => setData('current_password', e.target.value)}
                                    placeholder="Masukkan password lama"
                                    className="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-red-600 focus:ring-2 focus:ring-red-600/20 text-slate-900 dark:text-slate-100 rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none transition"
                                    required
                                />
                            </div>
                            {errors.current_password && (
                                <p className="text-red-600 dark:text-red-400 text-xs mt-1 font-medium">{errors.current_password}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Password Baru
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <Lock className="w-4 h-4" />
                                </div>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Minimal 6 karakter"
                                    className="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-red-600 focus:ring-2 focus:ring-red-600/20 text-slate-900 dark:text-slate-100 rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none transition"
                                    required
                                />
                            </div>
                            {errors.password && (
                                <p className="text-red-600 dark:text-red-400 text-xs mt-1 font-medium">{errors.password}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Konfirmasi Password Baru
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <Lock className="w-4 h-4" />
                                </div>
                                <input
                                    type="password"
                                    value={data.password_confirmation}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    placeholder="Ulangi password baru"
                                    className="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-red-600 focus:ring-2 focus:ring-red-600/20 text-slate-900 dark:text-slate-100 rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none transition"
                                    required
                                />
                            </div>
                        </div>

                        <div className="pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold py-2.5 px-4 rounded-xl shadow-md shadow-red-600/20 flex items-center justify-center gap-2 text-sm transition disabled:opacity-50"
                            >
                                <Save className="w-4 h-4" />
                                <span>{processing ? 'Menyimpan...' : 'Simpan Password Baru'}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
