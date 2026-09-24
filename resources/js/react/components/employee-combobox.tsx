import { Check, ChevronsUpDown } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export interface PersonOption {
    employee_biometric_id: number;
    biometric_employee_id: string | null;
    employee_no: string | null;
    employee_name: string;
    display_name: string;
    crosschex_id: string | null;
    group_name: string | null;
}

/** Searchable employee picker (name, employee no. or Bio ID). */
export function EmployeeCombobox({
    id,
    people,
    value,
    onChange,
    disabled = false,
    invalid = false,
    placeholder = 'Select payroll-active employee',
}: {
    id?: string;
    people: PersonOption[];
    value: number | null;
    onChange: (person: PersonOption | null) => void;
    disabled?: boolean;
    invalid?: boolean;
    placeholder?: string;
}) {
    const [open, setOpen] = useState(false);
    const selected = people.find((person) => person.employee_biometric_id === value) ?? null;

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    id={id}
                    type="button"
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    aria-invalid={invalid}
                    disabled={disabled}
                    className="w-full justify-between font-normal"
                >
                    <span className={cn('truncate', !selected && 'text-muted-foreground')}>
                        {selected
                            ? `${selected.display_name}${selected.employee_no ? ` · ${selected.employee_no}` : ''}`
                            : placeholder}
                    </span>
                    <ChevronsUpDown className="opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-(--radix-popover-trigger-width) p-0" align="start">
                <Command>
                    <CommandInput placeholder="Search name, employee no., or Bio ID…" />
                    <CommandList>
                        <CommandEmpty>No employee found.</CommandEmpty>
                        <CommandGroup>
                            {people.map((person) => (
                                <CommandItem
                                    key={person.employee_biometric_id}
                                    value={`${person.display_name} ${person.employee_no ?? ''} ${person.employee_biometric_id}`}
                                    onSelect={() => {
                                        onChange(person.employee_biometric_id === value ? null : person);
                                        setOpen(false);
                                    }}
                                >
                                    <div className="min-w-0 flex-1">
                                        <div className="truncate">{person.display_name}</div>
                                        <div className="text-xs text-muted-foreground">
                                            {person.employee_no ?? 'No employee no.'} · Bio ID {person.employee_biometric_id}
                                            {person.group_name ? ` · Group ${person.group_name}` : ''}
                                        </div>
                                    </div>
                                    <Check
                                        className={cn(
                                            'ml-auto',
                                            person.employee_biometric_id === value ? 'opacity-100' : 'opacity-0',
                                        )}
                                    />
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
