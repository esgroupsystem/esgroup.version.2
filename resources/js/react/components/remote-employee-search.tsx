import { Check, ChevronsUpDown, Loader2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export interface RemoteEmployee {
    employee_biometric_id: number;
    employee_no: string | null;
    employee_display_name: string;
    label: string;
}

/** Employee picker backed by a JSON search endpoint (`?q=`), debounced like the Blade version. */
export function RemoteEmployeeSearch({
    id,
    url,
    value,
    onChange,
    placeholder = 'Type employee name / employee no',
}: {
    id?: string;
    url: string;
    value: RemoteEmployee | null;
    onChange: (employee: RemoteEmployee | null) => void;
    placeholder?: string;
}) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<RemoteEmployee[]>([]);
    const [loading, setLoading] = useState(false);
    const [failed, setFailed] = useState(false);

    useEffect(() => {
        if (!open) return;

        const controller = new AbortController();
        const timer = window.setTimeout(() => {
            setLoading(true);
            setFailed(false);
            fetch(`${url}?q=${encodeURIComponent(query.trim())}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            })
                .then((response) => (response.ok ? response.json() : Promise.reject(response)))
                .then((data: RemoteEmployee[]) => setResults(data))
                .catch((error) => {
                    if (error?.name !== 'AbortError') setFailed(true);
                })
                .finally(() => setLoading(false));
        }, 300);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [open, query, url]);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button id={id} type="button" variant="outline" role="combobox" aria-expanded={open} className="w-full justify-between font-normal">
                    <span className={cn('truncate', !value && 'text-muted-foreground')}>{value ? value.label : placeholder}</span>
                    <ChevronsUpDown className="opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-(--radix-popover-trigger-width) p-0" align="start">
                <Command shouldFilter={false}>
                    <CommandInput placeholder="Search name or employee no…" value={query} onValueChange={setQuery} />
                    <CommandList>
                        {loading && (
                            <div className="flex items-center gap-2 px-3 py-6 text-sm text-muted-foreground">
                                <Loader2 className="size-4 animate-spin" />
                                Searching…
                            </div>
                        )}
                        {!loading && failed && <div className="px-3 py-6 text-center text-sm text-destructive">Failed to load employees.</div>}
                        {!loading && !failed && <CommandEmpty>No employee found.</CommandEmpty>}
                        {!loading && (
                            <CommandGroup>
                                {results.map((employee) => (
                                    <CommandItem
                                        key={employee.employee_biometric_id}
                                        value={String(employee.employee_biometric_id)}
                                        onSelect={() => {
                                            onChange(employee);
                                            setOpen(false);
                                        }}
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate">{employee.employee_display_name}</div>
                                            <div className="text-xs text-muted-foreground">
                                                Employee No: {employee.employee_no ?? '-'} · Bio ID #{employee.employee_biometric_id}
                                            </div>
                                        </div>
                                        <Check className={cn('ml-auto', value?.employee_biometric_id === employee.employee_biometric_id ? 'opacity-100' : 'opacity-0')} />
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        )}
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
