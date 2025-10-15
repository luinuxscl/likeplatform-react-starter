import { ChevronRight } from 'lucide-react';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { useI18n } from '@/lib/i18n/I18nProvider';
import { resolveIcon } from '@/lib/icon-resolver';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
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

export function NavMainCollapsible({ items = [], label = 'Platform' }: { items: NavItem[]; label?: string }) {
  const page = usePage();
  const { t } = useI18n();

  return (
    <SidebarGroup className="px-2 py-0">
      <SidebarGroupLabel>{t(label)}</SidebarGroupLabel>
      <SidebarMenu>
        {items.map((item) => {
          const Icon = resolveIcon(item.icon);
          const hasSubItems = item.items && item.items.length > 0;

          if (hasSubItems) {
            return (
              <Collapsible key={item.title} asChild defaultOpen={false} className="group/collapsible">
                <SidebarMenuItem>
                  <CollapsibleTrigger asChild>
                    <SidebarMenuButton tooltip={{ children: t(item.title) }}>
                      {Icon && <Icon />}
                      <span>{t(item.title)}</span>
                      <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                  </CollapsibleTrigger>
                  <CollapsibleContent>
                    <SidebarMenuSub>
                      {item.items?.map((subItem) => {
                        const SubIcon = resolveIcon(subItem.icon);
                        return (
                          <SidebarMenuSubItem key={subItem.title}>
                            <SidebarMenuSubButton
                              asChild
                              isActive={page.url.startsWith(typeof subItem.href === 'string' ? subItem.href : subItem.href.url)}
                            >
                              <Link href={subItem.href} prefetch>
                                {SubIcon && <SubIcon />}
                                <span>{t(subItem.title)}</span>
                              </Link>
                            </SidebarMenuSubButton>
                          </SidebarMenuSubItem>
                        );
                      })}
                    </SidebarMenuSub>
                  </CollapsibleContent>
                </SidebarMenuItem>
              </Collapsible>
            );
          }

          return (
            <SidebarMenuItem key={item.title}>
              <SidebarMenuButton
                asChild
                isActive={page.url.startsWith(typeof item.href === 'string' ? item.href : item.href.url)}
                tooltip={{ children: t(item.title) }}
              >
                <Link href={item.href} prefetch>
                  {Icon && <Icon />}
                  <span>{t(item.title)}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          );
        })}
      </SidebarMenu>
    </SidebarGroup>
  );
}
