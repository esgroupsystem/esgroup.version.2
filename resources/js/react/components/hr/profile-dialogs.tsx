import { useForm } from '@inertiajs/react';
import { Eye, Loader2, Save } from 'lucide-react';
import { useEffect, useRef, useState, type FormEvent, type ReactNode } from 'react';
import { FormField } from '@/components/form-field';
import { PhotoCropper } from '@/components/hr/photo-cropper';
import { useModal } from '@/components/modal/modal-context';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { DepartmentOption } from '@/lib/employee-status';
import { cn } from '@/lib/utils';

const NONE = 'none';

function Shell({ open, title, description, wide, processing, onClose, onSubmit, submitLabel, children }: {
    open: boolean;
    title: string;
    description?: string;
    wide?: boolean;
    processing: boolean;
    onClose: () => void;
    onSubmit: (event: FormEvent) => void;
    submitLabel: string;
    children: ReactNode;
}) {
    return (
        <Dialog open={open} onOpenChange={(value) => !value && !processing && onClose()}>
            <DialogContent className={cn('max-h-[90vh] overflow-y-auto', wide && 'sm:max-w-3xl')}>
                <form onSubmit={onSubmit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>{title}</DialogTitle>
                        {description && <DialogDescription>{description}</DialogDescription>}
                    </DialogHeader>
                    {children}
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose} disabled={processing}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? <Loader2 className="animate-spin" /> : <Save />}
                            {submitLabel}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function Choice({ id, value, options, onChange, placeholder, invalid, disabled }: {
    id: string;
    value: string;
    options: { value: string; label: string }[];
    onChange: (value: string) => void;
    placeholder: string;
    invalid?: boolean;
    disabled?: boolean;
}) {
    return (
        <Select value={value || NONE} onValueChange={(next) => onChange(next === NONE ? '' : next)} disabled={disabled}>
            <SelectTrigger id={id} className="w-full" aria-invalid={invalid}>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value={NONE}>{placeholder}</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

export interface ProfileValues {
    employee_id_permanent: string;
    full_name: string;
    date_of_birth: string;
    status: string;
    date_hired: string;
    company: string;
    department_id: string;
    position_id: string;
    garage: string;
    email: string;
    phone_number: string;
    address_1: string;
    address_2: string;
    emergency_name: string;
    emergency_contact: string;
}

export function EditProfileDialog({ open, onClose, values, employeeId, photoUrl, departments, statuses, companies, garages, url, checkUrl }: {
    open: boolean;
    onClose: () => void;
    values: ProfileValues;
    employeeId: number;
    photoUrl: string | null;
    departments: DepartmentOption[];
    statuses: string[];
    companies: string[];
    garages: string[];
    url: string;
    checkUrl: string;
}) {
    const form = useForm({ ...values, remove_profile_picture: false, profile_picture_cropped: '', _method: 'put' });
    // Inside a modal page the save must stay on the page underneath (modal.visit).
    const modal = useModal();
    const errors = form.errors as Record<string, string>;
    const [photo, setPhoto] = useState<File | null>(null);
    const [idCheck, setIdCheck] = useState<{ exists: boolean; message: string } | null>(null);
    const positions = departments.find((department) => department.id === form.data.department_id)?.positions ?? [];

    const checkTimer = useRef<number | undefined>(undefined);

    // Live permanent-ID availability check, like the Blade AJAX validation.
    useEffect(() => {
        const value = form.data.employee_id_permanent.trim();
        if (!open || form.processing || value === '' || value === values.employee_id_permanent) {
            setIdCheck(null);
            return;
        }
        checkTimer.current = window.setTimeout(() => {
            fetch(`${checkUrl}?value=${encodeURIComponent(value)}&ignore_id=${employeeId}`, { headers: { Accept: 'application/json' } })
                .then((response) => response.json())
                .then(setIdCheck)
                .catch(() => setIdCheck(null));
        }, 400);

        return () => window.clearTimeout(checkTimer.current);
    }, [form.data.employee_id_permanent, form.processing, open, checkUrl, employeeId, values.employee_id_permanent]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        // A check request landing between the save redirect and the page reload
        // would consume the one-time success flash.
        window.clearTimeout(checkTimer.current);
        // Sent as POST + _method=PUT so the cropped image goes as multipart like the Blade form.
        form.post(url, modal.visit({ forceFormData: true, preserveScroll: true, onSuccess: onClose }));
    };

    const text = (key: keyof ProfileValues, label: string, props: Record<string, unknown> = {}) => (
        <FormField id={`profile-${key}`} label={label} required={props.required === true} error={errors[key]}>
            <Input id={`profile-${key}`} value={form.data[key]} aria-invalid={!!errors[key]} onChange={(event) => form.setData(key, event.target.value)} {...props} />
        </FormField>
    );

    return (
        <Shell open={open} title="Edit employee profile" wide processing={form.processing} onClose={onClose} onSubmit={submit} submitLabel="Save changes">
            <div className="grid gap-4 sm:grid-cols-2">
                <FormField
                    id="profile-employee_id_permanent"
                    label="Permanent ID"
                    error={errors.employee_id_permanent ?? (idCheck?.exists ? idCheck.message : undefined)}
                    hint={idCheck && !idCheck.exists ? idCheck.message : 'Digits only, up to 10.'}
                >
                    <Input
                        id="profile-employee_id_permanent"
                        inputMode="numeric"
                        maxLength={10}
                        value={form.data.employee_id_permanent}
                        aria-invalid={!!errors.employee_id_permanent || !!idCheck?.exists}
                        onChange={(event) => form.setData('employee_id_permanent', event.target.value.replace(/\D/g, ''))}
                    />
                </FormField>
                {text('full_name', 'Full name', { required: true })}
                {text('date_of_birth', 'Date of birth', { type: 'date' })}
                <FormField id="profile-status" label="Status" required error={errors.status}>
                    <Choice id="profile-status" value={form.data.status} options={statuses.map((status) => ({ value: status, label: status }))} onChange={(value) => form.setData('status', value)} placeholder="-- Select status --" />
                </FormField>
                {text('date_hired', 'Date hired', { type: 'date' })}
                <FormField id="profile-company" label="Company" required error={errors.company}>
                    <Choice id="profile-company" value={form.data.company} options={companies.map((company) => ({ value: company, label: company }))} onChange={(value) => form.setData('company', value)} placeholder="-- Select company --" invalid={!!errors.company} />
                </FormField>
                <FormField id="profile-department" label="Department" error={errors.department_id}>
                    <Choice
                        id="profile-department"
                        value={form.data.department_id}
                        options={departments.map((department) => ({ value: department.id, label: department.name }))}
                        onChange={(value) => form.setData({ ...form.data, department_id: value, position_id: '' })}
                        placeholder="-- Select department --"
                    />
                </FormField>
                <FormField id="profile-position" label="Position" error={errors.position_id}>
                    <Choice
                        id="profile-position"
                        value={form.data.position_id}
                        options={positions.map((position) => ({ value: position.id, label: position.title }))}
                        onChange={(value) => form.setData('position_id', value)}
                        placeholder="-- Select position --"
                        disabled={!form.data.department_id}
                    />
                </FormField>
                <FormField id="profile-garage" label="Garage" required error={errors.garage}>
                    <Choice id="profile-garage" value={form.data.garage} options={garages.map((garage) => ({ value: garage, label: garage }))} onChange={(value) => form.setData('garage', value)} placeholder="-- Select garage --" invalid={!!errors.garage} />
                </FormField>
                {text('email', 'Email', { type: 'email' })}
                {text('phone_number', 'Phone number', { inputMode: 'numeric', maxLength: 11, placeholder: '09171234567' })}
                {text('address_1', 'Address 1')}
                {text('address_2', 'Address 2')}
                {text('emergency_name', 'Emergency contact name')}
                {text('emergency_contact', 'Emergency contact number', { inputMode: 'numeric', maxLength: 11 })}
            </div>

            <div className="grid gap-3 rounded-lg border p-4 sm:grid-cols-[auto_1fr] sm:items-center">
                {photo ? (
                    <PhotoCropper file={photo} onChange={(dataUrl) => form.setData('profile_picture_cropped', dataUrl)} />
                ) : (
                    <div className="flex size-24 items-center justify-center overflow-hidden rounded-full border bg-muted text-xs text-muted-foreground">
                        {photoUrl && !form.data.remove_profile_picture ? <img src={photoUrl} alt="Current profile" className="size-full object-cover" /> : 'No photo'}
                    </div>
                )}
                <div className="grid gap-2">
                    <FormField id="profile-photo" label="Profile picture" error={errors.profile_picture_cropped ?? errors.profile_picture} hint="JPG, PNG or WEBP. You can crop after choosing.">
                        <Input
                            id="profile-photo"
                            type="file"
                            accept="image/*"
                            onChange={(event) => {
                                const file = event.target.files?.[0] ?? null;
                                setPhoto(file);
                                form.setData({ ...form.data, remove_profile_picture: false, profile_picture_cropped: '' });
                            }}
                        />
                    </FormField>
                    {photoUrl && !photo && (
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox checked={form.data.remove_profile_picture} onCheckedChange={(checked) => form.setData('remove_profile_picture', checked === true)} />
                            Remove current profile picture
                        </label>
                    )}
                </div>
            </div>
        </Shell>
    );
}

export interface AssetNumber {
    key: string;
    label: string;
    value: string | null;
    date: string | null;
    date_input: string;
}

export interface AssetFile {
    key: string;
    label: string;
    url: string | null;
    name: string | null;
    date: string | null;
}

export function Edit201Dialog({ open, onClose, numbers, files, url }: { open: boolean; onClose: () => void; numbers: AssetNumber[]; files: AssetFile[]; url: string }) {
    const initial: Record<string, string | File | null> = {};
    numbers.forEach((number) => {
        initial[`${number.key}_number`] = number.value ?? '';
        initial[`${number.key}_updated_at`] = number.date_input;
    });
    files.forEach((file) => (initial[file.key] = null));
    const form = useForm<Record<string, string | File | null>>(initial);
    // Inside a modal page the save must stay on the page underneath (modal.visit).
    const modal = useModal();
    const errors = form.errors as Record<string, string>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => Object.fromEntries(Object.entries(data).filter(([, value]) => value !== null)));
        form.post(url, modal.visit({ forceFormData: true, preserveScroll: true, onSuccess: onClose }));
    };

    return (
        <Shell open={open} title="Edit 201 file" wide processing={form.processing} onClose={onClose} onSubmit={submit} submitLabel="Save 201 file">
            <div className="grid gap-4 sm:grid-cols-2">
                {numbers.map((number) => (
                    <div key={number.key} className="contents">
                        <FormField id={`asset-${number.key}`} label={number.label} error={errors[`${number.key}_number`]}>
                            <Input id={`asset-${number.key}`} value={(form.data[`${number.key}_number`] as string) ?? ''} onChange={(event) => form.setData(`${number.key}_number`, event.target.value)} />
                        </FormField>
                        <FormField id={`asset-${number.key}-date`} label={`${number.label} date updated`} error={errors[`${number.key}_updated_at`]}>
                            <Input id={`asset-${number.key}-date`} type="date" value={(form.data[`${number.key}_updated_at`] as string) ?? ''} onChange={(event) => form.setData(`${number.key}_updated_at`, event.target.value)} />
                        </FormField>
                    </div>
                ))}
            </div>
            <div className="grid gap-4 sm:grid-cols-3">
                {files.map((file) => (
                    <FormField key={file.key} id={`asset-file-${file.key}`} label={file.label} error={errors[file.key]} hint={file.name ?? 'No file'}>
                        <div className="flex gap-2">
                            <Input id={`asset-file-${file.key}`} type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" onChange={(event) => form.setData(file.key, event.target.files?.[0] ?? null)} />
                            {file.url && (
                                <Button type="button" variant="outline" size="icon" asChild>
                                    <a href={file.url} target="_blank" rel="noopener" aria-label={`View ${file.label}`}>
                                        <Eye />
                                    </a>
                                </Button>
                            )}
                        </div>
                    </FormField>
                ))}
            </div>
        </Shell>
    );
}

export interface StatusDetailValues {
    date_resigned: string;
    type_of_status: string;
    last_duty: string;
    clearance_date: string;
    last_pay_status: string;
    last_pay_date: string;
}

export function StatusDetailsDialog({ open, onClose, values, statusTypes, url }: { open: boolean; onClose: () => void; values: StatusDetailValues; statusTypes: string[]; url: string }) {
    const form = useForm<StatusDetailValues>(values);
    // Inside a modal page the save must stay on the page underneath (modal.visit).
    const modal = useModal();
    const errors = form.errors as Record<string, string>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(url, modal.visit({ preserveScroll: true, onSuccess: onClose }));
    };

    const date = (key: keyof StatusDetailValues, label: string) => (
        <FormField id={`status-${key}`} label={label} error={errors[key]}>
            <Input id={`status-${key}`} type="date" value={form.data[key]} onChange={(event) => form.setData(key, event.target.value)} />
        </FormField>
    );

    return (
        <Shell open={open} title="Edit employee status details" processing={form.processing} onClose={onClose} onSubmit={submit} submitLabel="Save">
            <div className="grid gap-4 sm:grid-cols-2">
                <FormField id="status-type" label="Type of status" error={errors.type_of_status}>
                    <Choice id="status-type" value={form.data.type_of_status} options={statusTypes.map((type) => ({ value: type, label: type }))} onChange={(value) => form.setData('type_of_status', value)} placeholder="— Select —" />
                </FormField>
                {date('date_resigned', 'Date status')}
                {date('last_duty', 'Last duty')}
                {date('clearance_date', 'Clearance date')}
                <FormField id="status-last-pay" label="Last pay status" error={errors.last_pay_status}>
                    <Choice
                        id="status-last-pay"
                        value={form.data.last_pay_status}
                        options={[
                            { value: 'Not released', label: 'Not released' },
                            { value: 'Released', label: 'Released' },
                        ]}
                        onChange={(value) => form.setData('last_pay_status', value)}
                        placeholder="— Select —"
                    />
                </FormField>
                {date('last_pay_date', 'Last pay date')}
            </div>
        </Shell>
    );
}

export function UploadAttachmentDialog({ open, onClose, url }: { open: boolean; onClose: () => void; url: string }) {
    const form = useForm<{ attachment: File | null }>({ attachment: null });
    // Inside a modal page the save must stay on the page underneath (modal.visit).
    const modal = useModal();

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(
            url,
            modal.visit({
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onClose();
                },
            }),
        );
    };

    return (
        <Shell open={open} title="Upload attachment" description="PDF, Office documents, text or images." processing={form.processing} onClose={onClose} onSubmit={submit} submitLabel="Upload">
            <FormField id="attachment-file" label="File" required error={form.errors.attachment}>
                <Input id="attachment-file" type="file" required onChange={(event) => form.setData('attachment', event.target.files?.[0] ?? null)} />
            </FormField>
        </Shell>
    );
}

