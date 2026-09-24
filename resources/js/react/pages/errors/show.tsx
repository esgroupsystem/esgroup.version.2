import { Head } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useEffect } from 'react';
import { notify } from '@/lib/notify';

interface Props {
    status: { code: number; title: string; message: string };
    /** A deliberate abort message (403 / 409 / 429), shown instead of the generic text. */
    detail: string | null;
    primary: { label: string; href: string };
    fallback: string;
    logo: string;
}

/**
 * Full-page HTTP error (a browser visit that failed). Errors on in-app
 * navigation stay toasts; this page is for direct loads, bookmarks and links.
 */
export default function ErrorPage({ status, detail, primary, fallback, logo }: Props) {
    const message = detail ?? status.message;

    useEffect(() => {
        notify(status.code >= 500 ? 'error' : 'warning', `${status.code} ${status.title}`, message);
    }, [status.code, status.title, message]);

    const back = () => {
        if (window.history.length > 1) window.history.back();
        else window.location.href = fallback;
    };

    return (
        <main
            className="grid min-h-svh place-items-center px-4 py-6 text-slate-900"
            style={{
                background:
                    'radial-gradient(1200px 600px at 100% -10%, rgba(37,99,235,.10), transparent 60%), radial-gradient(900px 500px at -10% 110%, rgba(14,165,233,.10), transparent 60%), #f8fafc',
            }}
        >
            <Head title={`${status.code} ${status.title}`} />
            <div className="w-full max-w-[520px] rounded-2xl border border-slate-200 bg-white px-9 pt-10 pb-8 text-center shadow-[0_20px_50px_-24px_rgba(15,23,42,.35)]">
                <div className="mb-7 inline-flex items-center gap-2.5 text-[13px] font-bold tracking-wide text-slate-700 uppercase">
                    <img src={logo} alt="" className="size-8 object-contain" />
                    Jell Group of Company
                </div>

                <p className="bg-gradient-to-br from-blue-700 to-sky-500 bg-clip-text text-7xl leading-none font-extrabold tracking-tighter text-transparent">{status.code}</p>
                <h1 className="mt-3 mb-2 text-[22px] font-bold tracking-tight">{status.title}</h1>
                <p className="mx-auto max-w-[380px] text-[15px] leading-relaxed text-slate-500">{message}</p>

                <div className="mt-7 flex flex-wrap justify-center gap-2.5">
                    <button
                        type="button"
                        onClick={back}
                        className="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-medium hover:bg-slate-100"
                    >
                        <ArrowLeft className="size-4" />
                        Go back
                    </button>
                    {/* A real link: the target may be outside the React app (e.g. after a 419 or maintenance). */}
                    <a href={primary.href} className="inline-flex h-10 items-center rounded-lg bg-[#1d63ed] px-4 text-sm font-medium text-white hover:bg-[#1552cc]">
                        {primary.label}
                    </a>
                </div>

                <p className="mt-6 text-xs text-slate-400">Need help? Contact the IT Department.</p>
            </div>
        </main>
    );
}
