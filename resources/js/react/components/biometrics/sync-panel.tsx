import { router } from '@inertiajs/react';
import { CheckCheck, CheckCircle2, CircleDashed, CloudDownload, RefreshCw, Zap, Copy } from 'lucide-react';
import { useCallback, useEffect, useRef, useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { csrfToken } from '@/lib/format';
import { TOAST_DURATION } from '@/lib/notify';
import { cn } from '@/lib/utils';

export interface SyncAccount {
    key: string;
    name: string;
}

interface AccountStat {
    name?: string;
    done?: boolean;
    page_count?: number;
    pages_done?: number;
    inserted?: number;
    skipped?: number;
}

interface SyncState {
    ok?: boolean;
    jobId?: string;
    state?: string;
    message?: string;
    accountName?: string;
    page?: number;
    pageCount?: number;
    saved?: number;
    skipped?: number;
    invalid?: number;
    percent?: number;
    retryAfter?: number;
    accountStats?: Record<string, AccountStat>;
    error?: string;
    done?: boolean;
    errors?: Record<string, string[]>;
}

const RESUME_KEY = 'crosschex_browser_sync_job_id';
const sleep = (ms: number) => new Promise((resolve) => window.setTimeout(resolve, ms));

const storage = {
    get: () => {
        try {
            return window.localStorage.getItem(RESUME_KEY);
        } catch {
            return null;
        }
    },
    set: (value: string) => {
        try {
            window.localStorage.setItem(RESUME_KEY, value);
        } catch {
            /* resume is a convenience only */
        }
    },
    clear: () => {
        try {
            window.localStorage.removeItem(RESUME_KEY);
        } catch {
            /* ignore */
        }
    },
};

async function readJson(response: Response): Promise<SyncState> {
    const raw = await response.text();

    try {
        return JSON.parse(raw) as SyncState;
    } catch {
        throw new Error('The server returned a non-JSON response. Check storage/logs/laravel.log.');
    }
}

const jsonHeaders = () => ({
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken(),
});

/** Browser-driven CrossChex sync: start a job, then call sync-step until it reports done (same flow as the Blade page). */
export function SyncPanel({
    accounts,
    canSync,
    today,
    urls,
}: {
    accounts: SyncAccount[];
    canSync: boolean;
    today: string;
    urls: { syncStart: string; syncStep: string; syncStatus: string };
}) {
    const [range, setRange] = useState({ from: today, to: today });
    const [selected, setSelected] = useState<string[]>(accounts.map((account) => account.key));
    const [open, setOpen] = useState(false);
    const [running, setRunning] = useState(false);
    const [status, setStatus] = useState<SyncState>({});
    const [statusText, setStatusText] = useState('Preparing...');
    const [error, setError] = useState('');
    const runningRef = useRef(false);

    // Sync failures are messages: show them as a toast (one id, so repeated polls don't stack).
    useEffect(() => {
        if (error) toast.error('Biometrics sync failed', { id: 'biometrics-sync-error', description: error, duration: TOAST_DURATION });
    }, [error]);

    const apply = (data: SyncState) => {
        setStatus(data);
        setStatusText(data.message || data.state || 'Synchronizing...');
        setError(data.error || '');
    };

    const runSteps = useCallback(
        async (jobId: string) => {
            if (runningRef.current) return;
            runningRef.current = true;
            setRunning(true);

            try {
                while (runningRef.current) {
                    const response = await fetch(urls.syncStep, { method: 'POST', headers: jsonHeaders(), body: JSON.stringify({ job: jobId }) });
                    if (response.status === 419) throw new Error('Session expired. Refresh the page and run the sync again.');
                    const data = await readJson(response);
                    apply(data);

                    if (data.state === 'error' || data.error) {
                        storage.clear();
                        return;
                    }

                    if (data.done) {
                        storage.clear();
                        toast.success('Biometrics sync finished.');
                        window.setTimeout(() => router.reload(), 900);
                        return;
                    }

                    await sleep(data.retryAfter && data.retryAfter > 0 ? data.retryAfter * 1000 : 150);
                }
            } catch (caught) {
                setError(caught instanceof Error ? caught.message : 'Biometric synchronization failed.');
                setStatusText('Sync interrupted.');
            } finally {
                runningRef.current = false;
                setRunning(false);
            }
        },
        [urls.syncStep],
    );

    // Resume a sync that was running when the page was refreshed.
    useEffect(() => {
        const jobId = storage.get();
        if (!jobId || !canSync) return;

        fetch(`${urls.syncStatus}?job=${encodeURIComponent(jobId)}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(async (response) => {
                const data = response.ok ? await readJson(response) : null;
                if (!data) {
                    storage.clear();
                    return;
                }
                apply(data);
                if (data.done || data.state === 'error') {
                    storage.clear();
                    return;
                }
                setOpen(true);
                void runSteps(jobId);
            })
            .catch(() => undefined);

        return () => {
            runningRef.current = false;
        };
    }, [canSync, runSteps, urls.syncStatus]);

    const start = async (keys: string[]) => {
        if (!range.from || !range.to) return toast.error('Please select Start Date and End Date.');
        if (range.to < range.from) return toast.error('End Date must be after or equal to Start Date.');
        if (!keys.length) return toast.error('Select at least one biometric source.');

        setError('');
        setStatus({});
        setStatusText('Starting synchronization...');
        setOpen(true);
        setRunning(true);

        try {
            const response = await fetch(urls.syncStart, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({ from: range.from, to: range.to, accounts: keys }),
            });
            const data = await readJson(response);

            if (!response.ok || !data.ok) {
                const validation = Object.values(data.errors ?? {}).flat().join('\n');
                throw new Error(validation || data.message || `Unable to start sync (${response.status}).`);
            }

            apply(data);
            storage.set(data.jobId!);
            setRunning(false);
            await runSteps(data.jobId!);
        } catch (caught) {
            setError(caught instanceof Error ? caught.message : 'Unable to start biometric synchronization.');
            setStatusText('Unable to start sync.');
            setRunning(false);
        }
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        void start(selected);
    };

    const percent = Math.max(0, Math.min(100, Number(status.percent) || 0));
    const stats = Object.entries(status.accountStats ?? {});

    return (
        <Card>
            <CardHeader className="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                <div className="grid gap-1.5">
                    <CardTitle>Biometrics sync</CardTitle>
                    <CardDescription>Sync one, several, or all configured CrossChex biometric sources directly from this page.</CardDescription>
                </div>
                <div className="flex flex-wrap gap-2">
                    <Badge variant="outline" className="border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400">
                        <Zap />
                        No queue worker required
                    </Badge>
                    <Badge variant="outline" className="border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400">
                        <Copy />
                        Existing records ignored
                    </Badge>
                </div>
            </CardHeader>
            <CardContent>
                {!canSync ? (
                    <Alert>
                        <AlertDescription>
                            You can review attendance monitoring records, but your role does not have permission to run a biometric synchronization.
                        </AlertDescription>
                    </Alert>
                ) : accounts.length === 0 ? (
                    <Alert>
                        <AlertTitle>No CrossChex sources are fully configured.</AlertTitle>
                        <AlertDescription>
                            <p>
                                Add each source URL, API key, and API secret in your <code>.env</code>, then run <code>php artisan optimize:clear</code>.
                            </p>
                        </AlertDescription>
                    </Alert>
                ) : (
                    <form onSubmit={submit} className="grid gap-4">
                        <div className="grid gap-3 sm:grid-cols-[12rem_12rem_1fr] sm:items-end">
                            <div className="grid gap-1.5">
                                <Label htmlFor="sync-from">Start date</Label>
                                <Input id="sync-from" type="date" required value={range.from} onChange={(event) => setRange({ ...range, from: event.target.value })} />
                            </div>
                            <div className="grid gap-1.5">
                                <Label htmlFor="sync-to">End date</Label>
                                <Input id="sync-to" type="date" required value={range.to} onChange={(event) => setRange({ ...range, to: event.target.value })} />
                            </div>
                            <div className="flex gap-2 sm:justify-end">
                                <Button type="button" variant="outline" size="sm" onClick={() => setSelected(accounts.map((account) => account.key))}>
                                    <CheckCheck />
                                    Select all
                                </Button>
                                <Button type="button" variant="outline" size="sm" onClick={() => setSelected([])}>
                                    Clear
                                </Button>
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <span className="text-sm font-medium">Biometric sources</span>
                            <div className="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                                {accounts.map((account) => {
                                    const checked = selected.includes(account.key);

                                    return (
                                        <label
                                            key={account.key}
                                            className={cn(
                                                'flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-colors hover:border-primary/60',
                                                checked && 'border-primary/60 bg-accent/50',
                                            )}
                                        >
                                            <Checkbox
                                                className="mt-0.5"
                                                checked={checked}
                                                onCheckedChange={(value) =>
                                                    setSelected(value ? [...selected, account.key] : selected.filter((key) => key !== account.key))
                                                }
                                            />
                                            <span className="min-w-0">
                                                <span className="block text-sm font-medium">{account.name}</span>
                                                <span className="block truncate text-xs text-muted-foreground">CrossChex source: {account.key}</span>
                                            </span>
                                        </label>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <p className="text-xs text-muted-foreground">
                                Fast mode inserts new attendance transactions only. Duplicate CrossChex IDs already in the database are ignored by the database
                                unique index without updating the existing row.
                            </p>
                            <div className="flex shrink-0 gap-2">
                                <Button type="submit" size="sm" disabled={running}>
                                    <RefreshCw />
                                    Sync selected
                                </Button>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="secondary"
                                    disabled={running}
                                    onClick={() => {
                                        const all = accounts.map((account) => account.key);
                                        setSelected(all);
                                        void start(all);
                                    }}
                                >
                                    <CloudDownload />
                                    Sync all
                                </Button>
                            </div>
                        </div>
                    </form>
                )}
            </CardContent>

            <Dialog open={open} onOpenChange={(value) => !running && setOpen(value)}>
                <DialogContent className="sm:max-w-2xl" showCloseButton={!running} onInteractOutside={(event) => event.preventDefault()}>
                    <DialogHeader>
                        <DialogTitle>Biometrics sync</DialogTitle>
                        <DialogDescription>Browser-driven synchronization — no queue worker or cron job.</DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4">
                        <div className="flex flex-col justify-between gap-1 text-sm md:flex-row">
                            <span className="font-medium">{statusText}</span>
                            <span className="text-muted-foreground">Source: {status.accountName || '-'}</span>
                        </div>
                        <div className="h-3 overflow-hidden rounded-full bg-muted" role="progressbar" aria-valuenow={percent} aria-valuemin={0} aria-valuemax={100}>
                            <div className={cn('h-full rounded-full bg-primary transition-all', running && 'animate-pulse')} style={{ width: `${percent}%` }} />
                        </div>
                        <div className="-mt-2 text-right text-xs text-muted-foreground tabular-nums">{percent}%</div>

                        <div className="grid grid-cols-2 gap-2 md:grid-cols-4">
                            <Stat label="Page" value={status.page ? `${status.page}${status.pageCount ? ` / ${status.pageCount}` : ''}` : '-'} />
                            <Stat label="New records" value={Number(status.saved || 0).toLocaleString()} tone="text-emerald-600 dark:text-emerald-400" />
                            <Stat label="Already saved" value={Number(status.skipped || 0).toLocaleString()} tone="text-sky-600 dark:text-sky-400" />
                            <Stat label="Invalid skipped" value={Number(status.invalid || 0).toLocaleString()} tone="text-amber-600 dark:text-amber-400" />
                        </div>

                        <div className="overflow-hidden rounded-lg border">
                            <div className="border-b bg-muted/50 px-3 py-2 text-xs font-medium">Source progress</div>
                            {stats.length === 0 && <div className="px-3 py-2 text-sm text-muted-foreground">Preparing sources...</div>}
                            {stats.map(([key, item]) => (
                                <div key={key} className="flex flex-col justify-between gap-1 border-b px-3 py-2 text-sm last:border-b-0 md:flex-row md:items-center">
                                    <span className="flex items-center gap-2 font-medium">
                                        {item.done ? <CheckCircle2 className="size-4 text-emerald-600" /> : <CircleDashed className="size-4 animate-spin text-muted-foreground" />}
                                        {item.name || key}
                                    </span>
                                    <span className={cn('text-xs tabular-nums', item.done ? 'text-emerald-600 dark:text-emerald-400' : 'text-muted-foreground')}>
                                        Pages {Number(item.pages_done || 0)} / {item.page_count ? Number(item.page_count) : '-'} · New{' '}
                                        {Number(item.inserted || 0).toLocaleString()} · Ignored {Number(item.skipped || 0).toLocaleString()}
                                    </span>
                                </div>
                            ))}
                        </div>

                        <Alert>
                            <AlertDescription>
                                Keep this page open while synchronization is running. If the page is refreshed, the browser will attempt to resume the current sync
                                session. Re-running the same range is safe because existing records are ignored.
                            </AlertDescription>
                        </Alert>
                        {!!status.retryAfter && status.retryAfter > 0 && (
                            <Alert>
                                <AlertDescription>CrossChex requested a short pause. Retrying automatically in {status.retryAfter} second(s)...</AlertDescription>
                            </Alert>
                        )}
                    </div>

                    <DialogFooter>
                        <Button variant="outline" disabled={running} onClick={() => setOpen(false)}>
                            Close
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </Card>
    );
}

function Stat({ label, value, tone }: { label: string; value: string; tone?: string }) {
    return (
        <div className="rounded-lg border p-3">
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className={cn('font-semibold tabular-nums', tone)}>{value}</div>
        </div>
    );
}
