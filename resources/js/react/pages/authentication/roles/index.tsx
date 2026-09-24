import { router, useForm } from '@inertiajs/react';
import { KeyRound, Layers, Loader2, Pencil, Plus, RefreshCw, Save, Search, ShieldAlert, ShieldCheck, Trash2, TriangleAlert, Users } from 'lucide-react';
import { useMemo, useState, type FormEvent } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { FormField } from '@/components/form-field';
import { PageHeader, StatCard } from '@/components/page-header';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';

interface Permission {
    id: number;
    name: string;
    module: string;
    module_label: string;
    action: string;
    action_label: string;
    description: string;
    risk: 'low' | 'medium' | 'high';
}

interface Role {
    id: number;
    name: string;
    users_count: number;
    permissions: string[];
    high_risk: number;
    medium_risk: number;
    update_url: string;
    destroy_url: string;
}

interface Props {
    roles: Role[];
    permissionGroups: { module: string; permissions: Permission[] }[];
    missingRoutePermissions: string[];
    stats: { roles: number; permissions: number; modules: number; users: number };
    can: { create: boolean; update: boolean; delete: boolean };
    urls: { store: string; sync: string };
}

const RISK_TONE: Record<string, string> = {
    low: 'text-muted-foreground',
    medium: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
    high: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
};

export default function RolesIndex({ roles, permissionGroups, missingRoutePermissions, stats, can, urls }: Props) {
    const [search, setSearch] = useState('');
    const [editing, setEditing] = useState<Role | 'new' | null>(null);
    const [syncing, setSyncing] = useState(false);

    const filtered = useMemo(() => {
        const needle = search.trim().toLowerCase();

        return needle ? roles.filter((role) => role.name.toLowerCase().includes(needle) || role.permissions.some((permission) => permission.includes(needle))) : roles;
    }, [roles, search]);

    const sync = () => router.post(urls.sync, {}, { preserveScroll: true, onStart: () => setSyncing(true), onFinish: () => setSyncing(false) });

    return (
        <AppLayout title="Roles">
            <PageHeader
                title="Roles & Permissions"
                description="Manage system access by role, module, action type, and sensitive transaction permission."
                actions={
                    <>
                        {can.update && (
                            <Button variant="outline" onClick={sync} disabled={syncing}>
                                {syncing ? <Loader2 className="animate-spin" /> : <RefreshCw />}
                                Sync permissions
                            </Button>
                        )}
                        {can.create && (
                            <Button onClick={() => setEditing('new')}>
                                <Plus />
                                Add role
                            </Button>
                        )}
                    </>
                }
            />

            {!can.update && (
                <Alert>
                    <ShieldCheck />
                    <AlertDescription>Roles are view-only for your account. Only a Developer can create, change, or delete roles.</AlertDescription>
                </Alert>
            )}

            {missingRoutePermissions.length > 0 && (
                <Alert>
                    <TriangleAlert />
                    <AlertTitle>Missing route permissions detected</AlertTitle>
                    <AlertDescription>
                        <p>These permissions exist in your route middleware but are not yet saved in your permissions table.</p>
                        <div className="mt-2 flex flex-wrap gap-1">
                            {missingRoutePermissions.map((permission) => (
                                <Badge key={permission} variant="outline" className="font-mono">
                                    {permission}
                                </Badge>
                            ))}
                        </div>
                        {can.update && (
                            <Button size="sm" className="mt-3" onClick={sync} disabled={syncing}>
                                <RefreshCw />
                                Create missing
                            </Button>
                        )}
                    </AlertDescription>
                </Alert>
            )}

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard label="Total roles" value={stats.roles.toLocaleString()} />
                <StatCard label="Permissions" value={stats.permissions.toLocaleString()} />
                <StatCard label="Modules" value={stats.modules.toLocaleString()} />
                <StatCard label="Users" value={stats.users.toLocaleString()} />
            </div>

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Roles</CardTitle>
                        <CardDescription>Each role lists its permissions and how many are high or medium risk.</CardDescription>
                    </div>
                    <div className="relative">
                        <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input aria-label="Search roles" placeholder="Search role name or permission..." className="w-72 pl-8" value={search} onChange={(event) => setSearch(event.target.value)} />
                    </div>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[900px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Role</TableHead>
                                <TableHead className="text-center">Users</TableHead>
                                <TableHead>Permissions</TableHead>
                                <TableHead>Risk</TableHead>
                                <TableHead className="pr-6 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtered.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-12 text-center text-muted-foreground">
                                        No roles found.
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtered.map((role) => (
                                <TableRow key={role.id} className="align-top">
                                    <TableCell className="pl-6">
                                        <div className="font-semibold">{role.name}</div>
                                        <div className="text-xs text-muted-foreground">{role.permissions.length} permission(s)</div>
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <Badge variant="secondary">
                                            <Users />
                                            {role.users_count}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="max-w-md whitespace-normal">
                                        {role.permissions.length === 0 ? (
                                            <span className="text-sm text-muted-foreground">No permissions assigned</span>
                                        ) : (
                                            <div className="flex flex-wrap gap-1">
                                                {role.permissions.slice(0, 5).map((permission) => (
                                                    <Badge key={permission} variant="outline" className="font-mono text-xs">
                                                        {permission}
                                                    </Badge>
                                                ))}
                                                {role.permissions.length > 5 && <Badge variant="secondary">+{role.permissions.length - 5} more</Badge>}
                                            </div>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex flex-wrap gap-1">
                                            {role.high_risk > 0 ? (
                                                <Badge variant="outline" className={RISK_TONE.high}>
                                                    {role.high_risk} high risk
                                                </Badge>
                                            ) : (
                                                <Badge variant="outline" className="border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400">
                                                    No high risk
                                                </Badge>
                                            )}
                                            {role.medium_risk > 0 && (
                                                <Badge variant="outline" className={RISK_TONE.medium}>
                                                    {role.medium_risk} medium
                                                </Badge>
                                            )}
                                        </div>
                                    </TableCell>
                                    <TableCell className="pr-6">
                                        <div className="flex justify-end gap-1">
                                            {can.update && (
                                                <Button variant="ghost" size="icon" className="size-8" aria-label={`Edit ${role.name}`} onClick={() => setEditing(role)}>
                                                    <Pencil />
                                                </Button>
                                            )}
                                            {can.delete && role.name !== 'Developer' && (
                                                <ConfirmAction
                                                    title={`Delete ${role.name}?`}
                                                    description={role.users_count > 0 ? `This role is still assigned to ${role.users_count} user(s) and cannot be deleted until they are moved to another role.` : 'This role will be permanently deleted.'}
                                                    confirmLabel="Delete"
                                                    destructive
                                                    onConfirm={() => router.delete(role.destroy_url, { preserveScroll: true })}
                                                    trigger={
                                                        <IconButton label={`Delete ${role.name}`}>
                                                            <Trash2 className="text-destructive" />
                                                        </IconButton>
                                                    }
                                                />
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            {editing && <RoleDialog role={editing === 'new' ? null : editing} groups={permissionGroups} total={stats.permissions} storeUrl={urls.store} onClose={() => setEditing(null)} />}
        </AppLayout>
    );
}

function RoleDialog({ role, groups, total, storeUrl, onClose }: { role: Role | null; groups: Props['permissionGroups']; total: number; storeUrl: string; onClose: () => void }) {
    const form = useForm({ name: role?.name ?? '', permissions: role?.permissions ?? [] });
    const [query, setQuery] = useState('');
    const [highOnly, setHighOnly] = useState(false);
    const errors = form.errors as Record<string, string>;
    const selected = new Set(form.data.permissions);

    const visibleGroups = useMemo(() => {
        const needle = query.trim().toLowerCase();

        return groups
            .map((group) => ({
                ...group,
                permissions: group.permissions.filter(
                    (permission) =>
                        (!highOnly || permission.risk === 'high') &&
                        (!needle || [permission.name, permission.module_label, permission.action_label, permission.description].some((text) => text.toLowerCase().includes(needle))),
                ),
            }))
            .filter((group) => group.permissions.length > 0);
    }, [groups, query, highOnly]);

    const setMany = (names: string[], on: boolean) => {
        const next = new Set(form.data.permissions);
        names.forEach((name) => (on ? next.add(name) : next.delete(name)));
        form.setData('permissions', [...next].sort());
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: onClose };
        if (role) {
            form.put(role.update_url, options);
        } else {
            form.post(storeUrl, options);
        }
    };

    const allNames = groups.flatMap((group) => group.permissions.map((permission) => permission.name));

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="max-h-[92vh] overflow-y-auto sm:max-w-5xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>{role ? `Edit role: ${role.name}` : 'Add role'}</DialogTitle>
                        <DialogDescription>Configure access by module, action, and risk level.</DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 md:grid-cols-[1fr_1fr_12rem] md:items-end">
                        <FormField id="role-name" label="Role name" required error={errors.name} hint="Use a clear name based on user responsibility.">
                            <Input id="role-name" required placeholder="Example: Maintenance Supervisor" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                        </FormField>
                        <FormField id="permission-search" label="Search permission">
                            <div className="relative">
                                <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="permission-search" className="pl-8" placeholder="Search module, action, rollback, export, delete..." value={query} onChange={(event) => setQuery(event.target.value)} />
                            </div>
                        </FormField>
                        <div className="rounded-lg border p-3">
                            <div className="text-xs text-muted-foreground">Selected permissions</div>
                            <div className="font-semibold tabular-nums">
                                {form.data.permissions.length} / {total}
                            </div>
                            <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-muted">
                                <div className="h-full rounded-full bg-primary" style={{ width: `${total ? (form.data.permissions.length / total) * 100 : 0}%` }} />
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <Button type="button" variant="outline" size="sm" onClick={() => setMany(allNames, true)}>
                            <ShieldCheck />
                            Select all
                        </Button>
                        <Button type="button" variant="outline" size="sm" onClick={() => form.setData('permissions', [])}>
                            Clear all
                        </Button>
                        <Button type="button" variant={highOnly ? 'default' : 'outline'} size="sm" onClick={() => setHighOnly(!highOnly)}>
                            <ShieldAlert />
                            {highOnly ? 'Show all' : 'High risk only'}
                        </Button>
                        <span className="ml-auto flex items-center gap-1 text-xs text-muted-foreground">
                            <KeyRound className="size-3" />
                            High-risk permissions (delete, finalize, rollback) should only go to trusted users.
                        </span>
                    </div>

                    {errors.permissions && <p className="text-xs text-destructive">{errors.permissions}</p>}

                    <div className="grid gap-3 md:grid-cols-2">
                        {visibleGroups.length === 0 && <p className="py-6 text-center text-sm text-muted-foreground md:col-span-2">No permissions match your search.</p>}
                        {visibleGroups.map((group) => {
                            const names = group.permissions.map((permission) => permission.name);
                            const checkedCount = names.filter((name) => selected.has(name)).length;
                            const allChecked = checkedCount === names.length;

                            return (
                                <div key={group.module} className="rounded-lg border">
                                    <div className="flex items-center justify-between gap-2 border-b bg-muted/40 px-3 py-2">
                                        <label className="flex cursor-pointer items-center gap-2 font-medium">
                                            <Checkbox checked={allChecked ? true : checkedCount > 0 ? 'indeterminate' : false} onCheckedChange={(value) => setMany(names, value === true)} />
                                            <Layers className="size-4 text-muted-foreground" />
                                            {group.module}
                                        </label>
                                        <span className="text-xs text-muted-foreground tabular-nums">
                                            {checkedCount}/{names.length}
                                        </span>
                                    </div>
                                    <div className="grid gap-1 p-2">
                                        {group.permissions.map((permission) => (
                                            <label
                                                key={permission.name}
                                                className={cn('flex cursor-pointer items-start gap-2 rounded-md px-2 py-1.5 hover:bg-accent/50', selected.has(permission.name) && 'bg-accent/40')}
                                                title={permission.description}
                                            >
                                                <Checkbox className="mt-0.5" checked={selected.has(permission.name)} onCheckedChange={(value) => setMany([permission.name], value === true)} />
                                                <span className="min-w-0 flex-1">
                                                    <span className="flex flex-wrap items-center gap-1.5 text-sm">
                                                        {permission.action_label}
                                                        <Badge variant="outline" className={cn('px-1.5 py-0 text-[10px] uppercase', RISK_TONE[permission.risk])}>
                                                            {permission.risk}
                                                        </Badge>
                                                    </span>
                                                    <span className="block truncate font-mono text-xs text-muted-foreground">{permission.name}</span>
                                                </span>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    <DialogFooter className="sticky -bottom-6 -mx-6 -mb-6 border-t bg-background px-6 py-4">
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing || !form.data.name.trim()}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            {role ? 'Save role' : 'Create role'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
