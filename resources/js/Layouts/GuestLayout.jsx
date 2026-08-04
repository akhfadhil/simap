import React from 'react';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen bg-[#f8fafc] dark:bg-[#0b0f19] text-[#0f172a] dark:text-[#f8fafc] flex flex-col justify-center items-center p-4 font-sans selection:bg-[#bb152c] selection:text-white transition-colors duration-200">
            {/* Subtle Grid Accent */}
            <div className="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] dark:bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:20px_20px] opacity-60 pointer-events-none" />

            <div className="w-full max-w-md relative z-10">
                {children}
            </div>

            <p className="mt-8 text-center text-xs text-[#94a3b8] dark:text-[#64748b] font-medium relative z-10">
                © {new Date().getFullYear()} SIMAP · KPU Kabupaten Banyuwangi
            </p>
        </div>
    );
}
