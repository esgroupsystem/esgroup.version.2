import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import type { ComponentProps, ReactNode } from 'react';
import { NavIcon } from '@/components/nav-icon';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { isNavParent, type NavGroup, type NavLink } from '@/types';

/**
 * React pages are visited client-side; pages not migrated yet are plain
 * links so Laravel serves the legacy Blade page with a full load.
 */
function NavAnchor({
    item,
    children,
    ...props
}: { item: NavLink; children: ReactNode } & Omit<ComponentProps<'a'>, 'href'>) {
    // `props` carries the classes/data attributes injected by the shadcn
    // `asChild` Slot; they must reach the <a> or the menu loses its styling.
    if (item.inertia) {
        return (
            <Link href={item.url} prefetch {...(props as Omit<ComponentProps<typeof Link>, 'href'>)}>
                {children}
            </Link>
        );
    }

    return (
        <a href={item.url} {...props}>
            {children}
        </a>
    );
}

/** Compact rows: 30px items with a 2px gap (32px pitch) and small uppercase section labels. */
const ITEM = 'h-[30px]';
const LABEL = 'h-7 text-[10.5px] font-semibold tracking-[0.08em] text-sidebar-foreground/50 uppercase';

export function NavMain({ groups }: { groups: NavGroup[] }) {
    return (
        <>
            {groups.map((group) => (
                <SidebarGroup key={group.label} className="py-1">
                    <SidebarGroupLabel className={LABEL}>{group.label}</SidebarGroupLabel>
                    <SidebarMenu className="gap-0.5">
                        {group.items.map((item) =>
                            isNavParent(item) ? (
                                <Collapsible
                                    key={item.title}
                                    asChild
                                    defaultOpen={item.isActive}
                                    className="group/collapsible"
                                >
                                    <SidebarMenuItem>
                                        <CollapsibleTrigger asChild>
                                            <SidebarMenuButton tooltip={item.title} isActive={item.isActive} className={ITEM}>
                                                <NavIcon name={item.icon} />
                                                <span>{item.title}</span>
                                                <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                                            </SidebarMenuButton>
                                        </CollapsibleTrigger>
                                        <CollapsibleContent>
                                            <SidebarMenuSub>
                                                {item.items.map((child) => (
                                                    <SidebarMenuSubItem key={child.url}>
                                                        <SidebarMenuSubButton asChild isActive={child.isActive} className="h-7">
                                                            <NavAnchor item={child}>
                                                                <span>{child.title}</span>
                                                            </NavAnchor>
                                                        </SidebarMenuSubButton>
                                                    </SidebarMenuSubItem>
                                                ))}
                                            </SidebarMenuSub>
                                        </CollapsibleContent>
                                    </SidebarMenuItem>
                                </Collapsible>
                            ) : (
                                <SidebarMenuItem key={item.url}>
                                    <SidebarMenuButton asChild tooltip={item.title} isActive={item.isActive} className={ITEM}>
                                        <NavAnchor item={item}>
                                            <NavIcon name={item.icon} />
                                            <span>{item.title}</span>
                                        </NavAnchor>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ),
                        )}
                    </SidebarMenu>
                </SidebarGroup>
            ))}
        </>
    );
}
