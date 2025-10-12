/**
 * Widget Dashboard System Types
 */

export interface Widget {
    key: string;
    component: string;
    title: string;
    description?: string;
    permission?: string;
    size: string;
    position: number;
    visible: boolean;
    config?: Record<string, any>;
    refresh_interval?: number;
    endpoint?: string;
    package: string;
}

export interface WidgetLayout {
    widgets: Widget[];
}

export interface WidgetConfig {
    [key: string]: any;
}

export interface WidgetState {
    isLoading: boolean;
    error: string | null;
    data: any;
    lastRefresh: string | null;
}

export interface WidgetProps {
    widget: Widget;
    onRefresh?: () => void;
    onToggleVisibility?: () => void;
    onConfigUpdate?: (config: WidgetConfig) => void;
}

export interface BaseWidgetProps {
    title: string;
    description?: string;
    isLoading?: boolean;
    error?: string | null;
    isEmpty?: boolean;
    onRefresh?: () => void;
    refreshInterval?: number;
    children: React.ReactNode;
    actions?: React.ReactNode;
}

export interface WidgetDashboardProps {
    layout?: 'grid' | 'masonry';
    columns?: number;
    allowCustomization?: boolean;
    className?: string;
}

export interface UseWidgetsReturn {
    widgets: Widget[];
    layout: Widget[];
    isLoading: boolean;
    error: string | null;
    refreshWidget: (key: string) => Promise<void>;
    toggleVisibility: (key: string) => Promise<void>;
    saveLayout: (widgets: Widget[]) => Promise<void>;
    resetLayout: () => Promise<void>;
    updateWidgetConfig: (key: string, config: WidgetConfig) => Promise<void>;
}

export interface WidgetApiResponse {
    widgets: Widget[];
}

export interface WidgetLayoutApiResponse {
    layout: Widget[];
}

export interface WidgetRefreshResponse {
    message: string;
    timestamp: string;
}

export interface WidgetToggleResponse {
    message: string;
    visible: boolean;
}

export interface WidgetConfigUpdateResponse {
    message: string;
    config: WidgetConfig;
}
