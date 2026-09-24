import { router, useForm } from '@inertiajs/react';
import { Check, Copy, KeyRound, Loader2, MoreHorizontal, Pencil, Power, Search, UserPlus, Users, X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { DataPagination } from '@/components/data-pagination';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { initials } from '@/lib/format';
import type { Paginated } from '@/types';

interface UserRow {
    id: number;
    full_name: string;
    username: string;
    email: string;
    role: string;
    role_name: string;
    location_id: string;
    account_status: string;
    last_online: string | null;
    updated_at: string | null;
    is_self: boolean;
    update_url: string;
    reset_url: string;
    status_url: string;
}

interface Props {
    users: Paginated<UserRow>;
    roles: string[];
    locations: { value: string; label: string }[];
    filters: { q: string };
    temporaryPassword: { password: string; username: string } | null;
    can: { create: boolean; update: boolean };
    urls: { index: string; store: string };
}

type Confirm = { user: UserRow; kind: 'reset' | 'status' };

export default function UsersIndex({ users, roles, locations, filters, temporaryPassword, can, urls }: Props) {
    const [search, setSearch] = useState(filters.q);
    // Saves redirect back to the unfiltered list; keep the box in step with it.
    useEffect(() => setSearch(filters.q), [filters.q]);
    const [editing, setEditing] = useState<UserRow | 'new' | null>(null);
    const [confirm, setConfirm] = useState<Confirm | null>(null);

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.index, search.trim() ? { q: search.trim() } : {});
    };

    return (
        <AppLayout title="Users">
            <PageHeader
                title="Users Directory"
                description="Manage user accounts, roles, access level, and login status."
                actions={
                    can.create && (
                        <Button onClick={() => setEditing('new')}>
                            <UserPlus />
                            Add user
                        </Button>
                    )
                }
            />

            {temporaryPassword && <TemporaryPassword {...temporaryPassword} />}

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>User list</CardTitle>
                        <CardDescription>{users.total.toLocaleString()} user(s)</CardDescription>
                    </div>
                    <form onSubmit={submitSearch} className="flex gap-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input aria-label="Search users" placeholder="Search name, username, email, role..." className="w-72 pl-8" value={search} onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        <Button type="submit" variant="outline">
                            Search
                        </Button>
                        {filters.q && (
                            <Button type="button" variant="ghost" onClick={() => router.get(urls.index)}>
                                <X />
                                Clear
                            </Button>
                        )}
                    </form>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[900px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Name</TableHead>
                                <TableHead>Username</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Last online</TableHead>
                                <TableHead>Updated</TableHead>
                                <TableHead className="pr-6 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {users.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="py-12">
                                        <div className="flex flex-col items-center gap-1 text-center text-muted-foreground">
                                            <Users className="size-8" />
                                            No users found.
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {users.data.map((user) => (
                                <TableRow key={user.id}>
                                    <TableCell className="pl-6">
                                        <div className="flex items-center gap-3">
                                            <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">{initials(user.full_name)}</div>
                                            <div className="min-w-0">
                                                <div className="font-medium">
                                                    {user.full_name}
                                                    {user.is_self && (
                                                        <Badge variant="secondary" className="ml-2">
                                                            You
                                                        </Badge>
                                                    )}
                                                </div>
                                                <div className="truncate text-xs text-muted-foreground">{user.email}</div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell className="font-mono text-sm">{user.username}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{user.role}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        {user.account_status === 'active' ? (
                                            <Badge variant="outline" className="border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400">
                                                Active
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline" className="border-red-300 text-red-700 dark:border-red-800 dark:text-red-400">
                                                Deactivated
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-sm">{user.last_online ?? <span className="text-muted-foreground">N/A</span>}</TableCell>
                                    <TableCell className="text-sm text-muted-foreground">{user.updated_at}</TableCell>
                                    <TableCell className="pr-6 text-right">
                                        {can.update && (
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" className="size-8" aria-label={`Actions for ${user.full_name}`}>
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem onSelect={() => setEditing(user)}>
                                                        <Pencil />
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onSelect={() => setConfirm({ user, kind: 'reset' })}>
                                                        <KeyRound />
                                                        Reset password
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem variant={user.account_status === 'active' ? 'destructive' : 'default'} onSelect={() => setConfirm({ user, kind: 'status' })}>
                                                        <Power />
                                                        {user.account_status === 'active' ? 'Deactivate' : 'Activate'}
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DataPagination paginator={users} noun="user" />

            {editing && <UserDialog user={editing === 'new' ? null : editing} roles={roles} locations={locations} storeUrl={urls.store} onClose={() => setEditing(null)} />}

            <AlertDialog open={!!confirm} onOpenChange={(open) => !open && setConfirm(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {confirm?.kind === 'reset' ? 'Reset password?' : confirm?.user.account_status === 'active' ? 'Deactivate account?' : 'Activate account?'}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {confirm?.kind === 'reset' ? (
                                <>
                                    A new temporary password will be generated for <strong>{confirm?.user.full_name}</strong>. They must change it on next login, and their mobile sessions are signed out.
                                </>
                            ) : confirm?.user.account_status === 'active' ? (
                                <>
                                    <strong>{confirm?.user.full_name}</strong> will no longer be able to sign in, and their mobile sessions are signed out.
                                </>
                            ) : (
                                <>
                                    <strong>{confirm?.user.full_name}</strong> will be able to sign in again.
                                </>
                            )}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            className={confirm?.kind === 'reset' || confirm?.user.account_status === 'active' ? 'bg-destructive text-white hover:bg-destructive/90' : undefined}
                            onClick={() => confirm && router.post(confirm.kind === 'reset' ? confirm.user.reset_url : confirm.user.status_url, {}, { preserveScroll: true })}
                        >
                            {confirm?.kind === 'reset' ? 'Reset now' : confirm?.user.account_status === 'active' ? 'Deactivate' : 'Activate'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}

function TemporaryPassword({ password, username }: { password: string; username: string }) {
    const [copied, setCopied] = useState(false);

    return (
        <Alert>
            <KeyRound />
            <AlertTitle>Temporary password for {username}</AlertTitle>
            <AlertDescription>
                <p>Give this password to the user through a secure channel. It will not be shown again after this page.</p>
                <div className="mt-2 flex items-center gap-2">
                    <code className="rounded-md border bg-muted px-3 py-1.5 font-mono text-base text-foreground select-all">{password}</code>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => {
                            void navigator.clipboard?.writeText(password).then(() => setCopied(true));
                        }}
                    >
                        {copied ? <Check /> : <Copy />}
                        {copied ? 'Copied' : 'Copy'}
                    </Button>
                </div>
            </AlertDescription>
        </Alert>
    );
}

const ANY = 'any';

function UserDialog({ user, roles, locations, storeUrl, onClose }: { user: UserRow | null; roles: string[]; locations: { value: string; label: string }[]; storeUrl: string; onClose: () => void }) {
    const form = useForm({
        full_name: user?.full_name ?? '',
        username: user?.username ?? '',
        email: user?.email ?? '',
        location_id: user?.location_id ?? '',
        role: user?.role_name || roles[0] || '',
        account_status: user?.account_status ?? 'active',
    });
    const errors = form.errors as Record<string, string>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(user ? user.update_url : storeUrl, { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="sm:max-w-2xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>{user ? 'Edit user' : 'Add user'}</DialogTitle>
                    </DialogHeader>
                    {!user && (
                        <p className="text-sm text-muted-foreground">A temporary password is generated automatically and shown once after saving. The user must change it on first login.</p>
                    )}
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField id="user-full_name" label="Full name" required error={errors.full_name}>
                            <Input id="user-full_name" required value={form.data.full_name} onChange={(event) => form.setData('full_name', event.target.value)} />
                        </FormField>
                        <FormField id="user-username" label="Username" required error={errors.username} hint="Letters, numbers, dashes and underscores.">
                            <Input id="user-username" required autoComplete="off" value={form.data.username} onChange={(event) => form.setData('username', event.target.value)} />
                        </FormField>
                        <FormField id="user-email" label="Email" required error={errors.email}>
                            <Input id="user-email" type="email" required value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} />
                        </FormField>
                        <FormField id="user-location" label="Assigned garage / location" error={errors.location_id}>
                            <Select value={form.data.location_id || ANY} onValueChange={(value) => form.setData('location_id', value === ANY ? '' : value)}>
                                <SelectTrigger id="user-location" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ANY}>All locations / no restriction</SelectItem>
                                    {locations.map((location) => (
                                        <SelectItem key={location.value} value={location.value}>
                                            {location.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormField>
                        <FormField id="user-role" label="Role" required error={errors.role}>
                            <Select value={form.data.role} onValueChange={(value) => form.setData('role', value)}>
                                <SelectTrigger id="user-role" className="w-full" aria-invalid={!!errors.role}>
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>
                                <SelectContent>
                                    {roles.map((role) => (
                                        <SelectItem key={role} value={role}>
                                            {role}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormField>
                        {user && (
                            <FormField id="user-status" label="Account status" required error={errors.account_status}>
                                <Select value={form.data.account_status} onValueChange={(value) => form.setData('account_status', value)}>
                                    <SelectTrigger id="user-status" className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="deactivated">Deactivated</SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormField>
                        )}
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Close
                        </Button>
                        <Button type="submit" disabled={form.processing || !form.data.role}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            Save
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
