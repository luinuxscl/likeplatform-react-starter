/**
 * Settings types for package configuration system
 */

export type FieldType = 
    | 'text'
    | 'textarea'
    | 'number'
    | 'boolean'
    | 'select'
    | 'color'
    | 'date'
    | 'time'
    | 'file'
    | 'json';

export interface FieldSchema {
    type: FieldType;
    label: string;
    description?: string;
    default: any;
    validation?: string;
    section?: string;
    options?: Record<string, string>;
    encrypted?: boolean;
    placeholder?: string;
    help?: string;
    min?: number;
    max?: number;
    step?: number;
    accept?: string;
    rows?: number;
}

export interface SettingsSchema {
    schema: Record<string, FieldSchema>;
    sections?: Record<string, string>;
    permissions?: {
        view?: string;
        edit?: string;
    };
}

export interface PackageSettings {
    name: string;
    schema: SettingsSchema;
    settings: Record<string, any>;
    hasSettings: boolean;
}

export interface SettingsFormData {
    settings: Record<string, any>;
}

export interface SettingsContextValue {
    packages: Record<string, PackageSettings>;
    currentPackage: string | null;
    setCurrentPackage: (packageName: string) => void;
    updateSettings: (packageName: string, settings: Record<string, any>) => Promise<boolean>;
    resetSettings: (packageName: string) => Promise<boolean>;
    loading: boolean;
    errors: Record<string, string[]>;
}
