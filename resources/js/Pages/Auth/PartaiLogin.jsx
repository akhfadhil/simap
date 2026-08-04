import React, { useState } from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { Lock, User, Eye, EyeOff, Flag, ArrowRight } from 'lucide-react';

export default function PartaiLogin() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
    });

    const [showPassword, setShowPassword] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/partai/login');
    };

    return (
        <GuestLayout>
            <Head title="Login Partai Politik" />

            <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-8 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_25px_-5px_rgba(0,0,0,0.4)]">
                {/* Header */}
                <div className="flex flex-col items-center text-center mb-8">
                    <div className="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 p-2.5 flex items-center justify-center mb-3 border border-indigo-100 dark:border-indigo-900/50 text-[#4f46e5] dark:text-[#6366f1]">
                        <Flag className="w-7 h-7" />
                    </div>
                    <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight">
                        Portal Partai Politik
                    </h1>
                    <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                        Monitoring Perolehan Suara · KPU Banyuwangi
                    </p>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                            Username Akun Partai
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94a3b8]">
                                <User className="w-4 h-4" />
                            </div>
                            <input
                                type="text"
                                value={data.username}
                                onChange={(e) => setData('username', e.target.value)}
                                placeholder="Username khusus partai"
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] focus:border-[#4f46e5] dark:focus:border-[#6366f1] focus:ring-2 focus:ring-[#4f46e5]/15 text-[#0f172a] dark:text-[#f8fafc] rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none transition duration-150"
                                required
                                autoFocus
                            />
                        </div>
                        {errors.username && (
                            <p className="text-red-600 dark:text-red-400 text-xs mt-1 font-medium">{errors.username}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                            Password
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94a3b8]">
                                <Lock className="w-4 h-4" />
                            </div>
                            <input
                                type={showPassword ? 'text' : 'password'}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Masukkan password"
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] focus:border-[#4f46e5] dark:focus:border-[#6366f1] focus:ring-2 focus:ring-[#4f46e5]/15 text-[#0f172a] dark:text-[#f8fafc] rounded-xl pl-10 pr-10 py-2.5 text-sm outline-none transition duration-150"
                                required
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#94a3b8] hover:text-[#475569] dark:hover:text-[#f8fafc]"
                            >
                                {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                            </button>
                        </div>
                        {errors.password && (
                            <p className="text-red-600 dark:text-red-400 text-xs mt-1 font-medium">{errors.password}</p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-[#4f46e5] hover:bg-[#4338ca] active:bg-[#3730a3] text-white font-semibold py-2.5 px-4 rounded-xl shadow-md shadow-[#4f46e5]/20 flex items-center justify-center gap-2 text-sm transition duration-150 disabled:opacity-50 mt-2 cursor-pointer"
                    >
                        <span>{processing ? 'Memproses...' : 'Masuk Portal Partai'}</span>
                        <ArrowRight className="w-4 h-4" />
                    </button>
                </form>

                {/* Footer link */}
                <div className="mt-6 pt-5 border-t border-[#f1f5f9] dark:border-[#1e293b] text-center">
                    <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                        Bukan akun partai?{' '}
                        <Link href="/" className="text-[#4f46e5] dark:text-[#6366f1] hover:underline font-semibold ml-0.5">
                            Kembali ke Login Utama →
                        </Link>
                    </p>
                </div>
            </div>
        </GuestLayout>
    );
}
