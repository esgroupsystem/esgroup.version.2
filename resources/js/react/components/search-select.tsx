import { Check, ChevronsUpDown } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export interface SearchOption {
    value: string;
    label: string;
    hint?: string;
}

/** Searchable single-select over a preloaded option list (replaces Choices.js selects). */
export function SearchSelect({
    id,
    options,
    value,
    onChange,
    placeholder = 'Select...',
    searchPlaceholder = 'Search...',
    empty = 'Nothing found.',
    invalid = false,
    disabled = false,
    ariaLabel,
}: {
    id?: string;
    options: SearchOption[];
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    searchPlaceholder?: string;
    empty?: string;
    invalid?: boolean;
    disabled?: boolean;
    ariaLabel?: string;
}) {
    const [open, setOpen] = useState(false);
    const selected = options.find((option) => option.value === value);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    id={id}
                    type="button"
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    aria-label={ariaLabel}
                    aria-invalid={invalid}
                    disabled={disabled}
                    className="w-full justify-between font-normal"
                >
                    <span className={cn('truncate', !selected && 'text-muted-foreground')}>{selected ? selected.label : placeholder}</span>
                    <ChevronsUpDown className="opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-(--radix-popover-trigger-width) p-0" align="start">
                <Command>
                    <CommandInput placeholder={searchPlaceholder} />
                    <CommandList>
                        <CommandEmpty>{empty}</CommandEmpty>
                        <CommandGroup>
                            {options.map((option) => (
                                <CommandItem
                                    key={option.value}
                                    value={`${option.label} ${option.hint ?? ''} ${option.value}`}
                                    onSelect={() => {
                                        onChange(option.value === value ? '' : option.value);
                                        setOpen(false);
                                    }}
                                >
                                    <div className="min-w-0 flex-1">
                                        <div className="truncate">{option.label}</div>
                                        {option.hint && <div className="truncate text-xs text-muted-foreground">{option.hint}</div>}
                                    </div>
                                    <Check className={cn('ml-auto', option.value === value ? 'opacity-100' : 'opacity-0')} />
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
