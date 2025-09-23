import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { useI18n } from '@/lib/i18n/I18nProvider';

export function NavMain({ items = [], label = 'Platform' }: { items: NavItem[]; label?: string }) {
  const page = usePage();
  const { t } = useI18n();
  return (
    <SidebarGroup className="px-2 py-0">
      <SidebarGroupLabel>{t(label)}</SidebarGroupLabel>
      <SidebarMenu>
        {items.map((item) => (
          <SidebarMenuItem key={item.title}>
            <SidebarMenuButton
              asChild
              isActive={page.url.startsWith(typeof item.href === 'string' ? item.href : item.href.url)}
              tooltip={{ children: t(item.title) }}
            >
              <Link href={item.href} prefetch>
                {item.icon && <item.icon />}
                <span>{t(item.title)}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        ))}
      </SidebarMenu>
    </SidebarGroup>
  );
}
