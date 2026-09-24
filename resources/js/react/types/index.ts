export interface AuthUser {
    id: number;
    name: string;
    username: string | null;
    email: string | null;
    role: string | null;
    account_status: string;
    must_change_password: boolean;
    last_online: string | null;
}

export interface NavLink {
    title: string;
    icon: string | null;
    url: string;
    isActive: boolean;
    /** true when the target page is a React page (client-side visit). */
    inertia: boolean;
}

export interface NavParent {
    title: string;
    icon: string | null;
    isActive: boolean;
    items: NavLink[];
}

export type NavItem = NavLink | NavParent;

export interface NavGroup {
    label: string;
    items: NavItem[];
}

export interface SharedData {
    appName: string;
    auth: { user: AuthUser | null };
    navigation: NavGroup[];
    routes: { logout: string; changePassword: string; lock: string };
    flash: {
        success: string | null;
        error: string | null;
        warning: string | null;
        info: string | null;
        messages: { message: string; level: string }[];
    };
    errors: Record<string, string>;
    [key: string]: unknown;
}

/** Laravel LengthAwarePaginator serialized to JSON. */
export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
}

export function isNavParent(item: NavItem): item is NavParent {
    return 'items' in item;
}
