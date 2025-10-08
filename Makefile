# Makefile de atajos para desarrollo

.PHONY: help dev build test fresh install clear packages-list packages-clear packages-verify

help:
	@echo "Targets disponibles:"
	@echo "  dev              - Ejecuta Vite en modo desarrollo"
	@echo "  build            - Compila assets de producción"
	@echo "  test             - Ejecuta la suite de tests (Pest)"
	@echo "  install          - Instalación estándar (migrate + seed + optimize:clear)"
	@echo "  fresh            - Instalación de desarrollo (migrate:fresh --seed + optimize:clear)"
	@echo "  clear            - Limpia cachés de Laravel"
	@echo "  packages-list    - Lista packages de personalización descubiertos"
	@echo "  packages-clear   - Limpia caché del sistema de packages"
	@echo "  packages-verify  - Verifica instalación del sistema de packages"

dev:
	npm run dev

build:
	npm run build

test:
	vendor/bin/pest

install:
	php artisan app:install

fresh:
	php artisan app:install --dev

clear:
	php artisan optimize:clear

packages-list:
	php artisan customization:list-packages

packages-clear:
	php artisan customization:clear-cache

packages-verify:
	@bash scripts/verify-customization-system.sh
