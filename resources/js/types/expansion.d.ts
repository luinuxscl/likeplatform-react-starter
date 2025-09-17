declare global {
  interface ThemeConfig {
    name: string;
    colors: Record<string, string>;
  }

  interface ExpansionConfig {
    themes: {
      enabled: boolean;
      default_theme: string;
      available_themes: Record<string, ThemeConfig>;
      custom_themes: Record<string, ThemeConfig>;
    };
  }
}

export {}; // ensure this file is treated as a module
