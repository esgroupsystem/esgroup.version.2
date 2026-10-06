import { Link } from '@inertiajs/react';
import { Calculator, ListChecks, SlidersHorizontal } from 'lucide-react';
import { useModal } from '@/components/modal/modal-context';
import { cn } from '@/lib/utils';
import type { SettingsTabUrls } from './types';

const TABS = [
    { key: 'settings', label: 'Rates & contributions', icon: SlidersHorizontal },
    { key: 'rules', label: 'Custom rules', icon: ListChecks },
    { key: 'test', label: 'Test computation', icon: Calculator },
] as const;

/** Tab bar shared by the three Payroll Settings pages (each tab is its own page). */
export function SettingsTabs({ active, urls }: { active: keyof SettingsTabUrls; urls: SettingsTabUrls }) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <nav aria-label="Payroll settings" className="inline-flex max-w-full items-center justify-start gap-1 overflow-x-auto rounded-lg bg-muted p-1 text-muted-foreground">
            {TABS.map((tab) => (
                <Link
                    key={tab.key}
                    href={urls[tab.key]}
                    aria-current={active === tab.key ? 'page' : undefined}
                    className={cn(
                        'inline-flex h-8 shrink-0 items-center gap-1.5 rounded-md px-3 text-sm font-medium whitespace-nowrap transition-colors',
                        active === tab.key ? 'bg-background text-foreground shadow-sm' : 'hover:text-foreground',
                    )}
                >
                    <tab.icon className="size-4" aria-hidden />
                    {tab.label}
                </Link>
            ))}
        </nav>
    );
}
