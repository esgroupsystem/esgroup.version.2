import { CloudUpload, FileIcon, X } from 'lucide-react';
import { useRef, useState, type DragEvent } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const size = (bytes: number) =>
    bytes >= 1024 * 1024 ? `${(bytes / 1024 / 1024).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;

/** Drag-and-drop / click-to-pick multi-file input with a removable preview list. */
export function FileDrop({
    id,
    files,
    onChange,
    accept,
    label = 'Drop your files here or click to browse',
    invalid = false,
}: {
    id?: string;
    files: File[];
    onChange: (files: File[]) => void;
    accept?: string;
    label?: string;
    invalid?: boolean;
}) {
    const input = useRef<HTMLInputElement>(null);
    const [over, setOver] = useState(false);

    const add = (list: FileList | null) => {
        if (list?.length) onChange([...files, ...Array.from(list)]);
    };

    const drop = (event: DragEvent) => {
        event.preventDefault();
        setOver(false);
        add(event.dataTransfer.files);
    };

    return (
        <div className="grid gap-2">
            <button
                type="button"
                id={id}
                aria-invalid={invalid}
                onClick={() => input.current?.click()}
                onDragOver={(event) => {
                    event.preventDefault();
                    setOver(true);
                }}
                onDragLeave={() => setOver(false)}
                onDrop={drop}
                className={cn(
                    'flex flex-col items-center gap-2 rounded-lg border border-dashed p-6 text-sm text-muted-foreground transition-colors hover:border-primary/60 hover:bg-accent/40',
                    over && 'border-primary bg-accent/60',
                    invalid && 'border-destructive',
                )}
            >
                <CloudUpload className="size-6" />
                {label}
            </button>
            <input
                ref={input}
                type="file"
                multiple
                accept={accept}
                className="hidden"
                onChange={(event) => {
                    add(event.target.files);
                    event.target.value = '';
                }}
            />
            {files.length > 0 && (
                <ul className="grid gap-1.5">
                    {files.map((file, index) => (
                        <li key={`${file.name}-${index}`} className="flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm">
                            {file.type.startsWith('image/') ? (
                                <img src={URL.createObjectURL(file)} alt="" className="size-8 rounded object-cover" />
                            ) : (
                                <FileIcon className="size-4 text-muted-foreground" />
                            )}
                            <span className="min-w-0 flex-1 truncate">{file.name}</span>
                            <span className="text-xs text-muted-foreground">{size(file.size)}</span>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="size-7"
                                aria-label={`Remove ${file.name}`}
                                onClick={() => onChange(files.filter((_, current) => current !== index))}
                            >
                                <X />
                            </Button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
