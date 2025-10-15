import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import fs from 'fs';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const packagesDir = path.resolve(__dirname, '../packages');
const entryCandidates = ['index.tsx', 'index.ts', 'index.jsx', 'index.js'];
const packageEntries = fs.existsSync(packagesDir)
    ? fs
          .readdirSync(packagesDir)
          .map((name) => {
              const packageDir = path.join(packagesDir, name);
              if (!fs.statSync(packageDir).isDirectory()) {
                  return null;
              }
              const resourcesDir = path.join(packageDir, 'resources/js');
              if (!fs.existsSync(resourcesDir)) {
                  return null;
              }
              const entry = entryCandidates.find((candidate) =>
                  fs.existsSync(path.join(resourcesDir, candidate)),
              );
              if (!entry) {
                  return null;
              }
              const entryPath = path.join(resourcesDir, entry);
              return {
                  name,
                  entry: entryPath,
                  alias: resourcesDir,
              };
          })
          .filter((value): value is { name: string; entry: string; alias: string } => Boolean(value))
    : [];

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.tsx',
                ...packageEntries.map((pkg) => pkg.entry),
            ],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    resolve: {
        alias: {
            ...Object.fromEntries(packageEntries.map((pkg) => [`@${pkg.name}`, pkg.alias])),
        },
    },
    server: {
        fs: {
            allow: ['..'],
        },
        watch: {
            followSymlinks: true,
        },
    },
    esbuild: {
        jsx: 'automatic',
    },
});
