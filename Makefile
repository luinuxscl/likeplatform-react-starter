# Makefile de atajos para desarrollo

.PHONY: help dev build test fresh install clear

help:
	@echo "Targets disponibles:"
	@echo "  dev      - Ejecuta Vite en modo desarrollo"
	@echo "  build    - Compila assets de producción"
	@echo "  test     - Ejecuta la suite de tests (Pest)"
	@echo "  install  - Instalación estándar (migrate + seed + optimize:clear)"
	@echo "  fresh    - Instalación de desarrollo (migrate:fresh --seed + optimize:clear)"
	@echo "  clear    - Limpia cachés de Laravel"

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
