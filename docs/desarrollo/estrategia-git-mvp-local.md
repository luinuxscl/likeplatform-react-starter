# Estrategia Git MVP - Desarrollo Local con Laravel-Boost

## Enfoque MVP Simplificado

### Principios MVP
- **Repository Local**: Sin GitHub/CI/CD por ahora
- **Tests Pest únicamente**: Sin automation compleja
- **Laravel-Boost**: Para debugging y verification local
- **Workflow Simple**: Enfoque en productividad, no en proceso
- **MVP First**: Funcionalidad básica antes que perfección

## Estructura Git Simplificada

### Ramas MVP
```
main (versiones estables del MVP)
└── develop (development activo)
    └── feature/roles-basic (features MVP)
```

### Workflow MVP Local

#### 1. Setup Inicial
```bash
# Setup del proyecto local
git init
git add .
git commit -m "initial: Laravel React Expansion MVP setup"

# Crear develop branch
git checkout -b develop
git commit --allow-empty -m "feat: initialize develop branch"
```

#### 2. Development Workflow Simple
```bash
# Crear feature para MVP
git checkout develop
git checkout -b feature/roles-basic

# Desarrollo con Laravel-Boost (sin automation)
php artisan boost:mcp application-info  # Check estado
# ... código ...
vendor/bin/pest  # Solo tests Pest, nada más

# Commit simple
git add .
git commit -m "feat: add basic role management"
```

#### 3. Merge Simple (Sin PRs)
```bash
# Verificación manual simple
vendor/bin/pest                          # Tests Pest
php artisan boost:mcp last-error        # Check errores
php artisan boost:mcp read-log-entries 5 # Check logs

# Merge directo (tu autorización)
git checkout develop
git merge feature/roles-basic
git branch -d feature/roles-basic
```

#### 4. Release MVP
```bash
# Cuando esté listo el MVP
git checkout main
git merge develop
git tag v0.1.0-mvp
```

## Tests Strategy MVP

### Solo Tests Pest
```bash
# Simple test execution
vendor/bin/pest

# Test específicos
vendor/bin/pest tests/Unit/RoleTest.php
vendor/bin/pest tests/Feature/RoleManagementTest.php

# Con coverage básico
vendor/bin/pest --coverage
```

### Tests MVP Structure
```
tests/
├── Unit/               # Tests básicos de modelos
│   ├── RoleTest.php
│   └── PermissionTest.php
├── Feature/            # Tests de controllers
│   ├── RoleManagementTest.php
│   └── DashboardTest.php
└── TestCase.php        # Base test case simple
```

### Test Example MVP
```php
<?php
// tests/Feature/RoleManagementTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class RoleManagementTest extends TestCase
{
    public function test_can_create_role(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->post('/expansion/roles', [
                'name' => 'editor',
                'permissions' => ['edit posts']
            ]);
            
        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['name' => 'editor']);
    }
    
    public function test_can_view_roles_page(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/expansion/roles');
            
        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('expansion/roles/index')
        );
    }
}
```

## Laravel-Boost Integration MVP

### Simple Daily Workflow
```bash
# Inicio del día
php artisan boost:mcp application-info

# Durante development
php artisan boost:mcp last-error        # Si algo falla
php artisan boost:mcp tinker             # Probar código rápido
vendor/bin/pest                          # Tests básicos

# Antes de merge
php artisan boost:mcp read-log-entries 10
vendor/bin/pest
```

### Laravel-Boost Helpers MVP
```bash
# Aliases simples para desarrollo
alias boost-info="php artisan boost:mcp application-info"
alias boost-error="php artisan boost:mcp last-error"
alias boost-logs="php artisan boost:mcp read-log-entries 20"
alias boost-routes="php artisan boost:mcp list-routes"
alias boost-db="php artisan boost:mcp database-schema"
alias test-mvp="vendor/bin/pest"
```

## MVP Features Roadmap Simplificado

### Fase MVP 1: Basic Roles (1-2 semanas)
```bash
# Features mínimas
- Role model básico
- Permission model básico  
- UI React simple para listar roles
- Tests Pest básicos

# Branches
feature/role-model      # Backend básico
feature/role-ui         # React básico
```

### Fase MVP 2: Media Basic (1-2 semanas)
```bash
# Features mínimas
- Upload básico de archivos
- Lista simple de archivos
- Delete básico
- Tests Pest básicos

# Branches  
feature/media-upload    # Upload básico
feature/media-list      # Lista básica
```

### Fase MVP 3: Dashboard Basic (1 semana)
```bash
# Features mínimas
- Dashboard con placeholders reales
- 2-3 widgets básicos
- Tests Pest básicos

# Branches
feature/dashboard-basic # Dashboard MVP
```

## Scripts MVP Simples

### Makefile Simplificado
```makefile
# Makefile para MVP

.PHONY: test info error logs routes db

test:           ## Run Pest tests
	vendor/bin/pest

test-coverage:  ## Run tests with coverage
	vendor/bin/pest --coverage

info:           ## Laravel-Boost app info
	php artisan boost:mcp application-info

error:          ## Check last error
	php artisan boost:mcp last-error

logs:           ## Show recent logs
	php artisan boost:mcp read-log-entries 20

routes:         ## Show routes
	php artisan boost:mcp list-routes

db:             ## Show database schema
	php artisan boost:mcp database-schema

quick-check:    ## Quick development check
	@make error
	@make test
	@echo "✅ Quick check completed"

dev-status:     ## Development status
	@echo "🔍 Development Status Check"
	@make info
	@make routes
	@make error
```

### Development Scripts MVP
```bash
#!/bin/bash
# scripts/dev-check.sh

echo "🔍 MVP Development Check..."

# Simple checks
echo "1. Application Info:"
php artisan boost:mcp application-info

echo "2. Last Error:"
php artisan boost:mcp last-error

echo "3. Running Tests:"
vendor/bin/pest

echo "4. Routes Check:"
php artisan boost:mcp list-routes --path="expansion"

echo "✅ MVP Check Complete"
```

## Commit Strategy MVP

### Simple Semantic Commits
```bash
# Simple prefixes
feat: nueva funcionalidad
fix: corrección de bug
test: añadir/modificar tests
docs: documentación

# Examples MVP
git commit -m "feat: add basic Role model"
git commit -m "feat: add role list UI component"  
git commit -m "test: add role creation test"
git commit -m "fix: role validation error"
```

### No Complex Rules
- No conventional commits complejos
- No automatic changelogs
- No semantic versioning automático
- Solo tags manuales para MVPs: `v0.1.0-mvp`, `v0.2.0-mvp`

## Development Environment MVP

### Local Setup Simple
```bash
# Desarrollo local básico
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev

# Laravel-Boost verification
php artisan boost:mcp application-info
```

### No External Dependencies MVP
- No GitHub Actions
- No Docker (si no es necesario)
- No external CI/CD
- No automated deployments
- No complex tooling

## Quality Gates MVP

### Simple Quality Check
```bash
# Antes de merge (manual check)
1. vendor/bin/pest                    # Tests pasar
2. php artisan boost:mcp last-error   # Sin errores
3. Basic manual testing               # Funciona en browser
4. Tu aprobación                      # Tu review manual
```

### MVP Acceptance Criteria
- ✅ Tests Pest pasan
- ✅ Sin errores en Laravel-Boost
- ✅ Funcionalidad básica funciona
- ✅ No rompe funcionalidad existente

## Migration to Production Later

### When Ready for Production
```bash
# Futuro (no MVP)
- GitHub repository setup
- CI/CD si es necesario
- More complex testing
- Documentation completa
- Production deployment
```

### MVP to Production Path
1. **MVP Local** (ahora) → Funcionalidad básica
2. **GitHub + Tests** (después) → Colaboración
3. **CI/CD** (mucho después) → Automation si es necesario
4. **Production** (final) → Release público

## Conclusión MVP

### Focus MVP
- **Funcionalidad antes que proceso**
- **Tests Pest simples y efectivos**
- **Laravel-Boost para debugging local**
- **Git workflow simple y directo**
- **Sin overengineering**

### Success Criteria MVP
- ✅ Sistema de roles básico funcionando
- ✅ Upload de media básico funcionando  
- ✅ Dashboard con contenido real
- ✅ Tests Pest cubren funcionalidad crítica
- ✅ Integración seamless con starter kit

**Enfoque**: Build it, test it, ship it. Optimización y proceso complejo después del MVP.
