# QA Case — OpciónYo

Suite de pruebas automatizadas para la plataforma de bienestar **OpciónYo**, que conecta pacientes con especialistas a través de videollamadas.

Este proyecto implementa una estrategia completa de QA sobre un mock de la aplicación Laravel/Vue, cubriendo los flujos críticos de: autenticación, pagos con Stripe, agendamiento de sesiones y videollamadas con AWS Chime.

---

## Estructura del Proyecto

```
qa-case-opcionyo/
├── README.md                          # Este archivo
├── PROCESS.md                         # Herramientas, decisiones y proceso
├── plan/
│   └── PLAN.md                        # Plan de QA (estrategia, herramientas, DoD)
├── bugs/
│   └── BUGS.md                        # 3 bugs/edge cases documentados
├── tests/
│   ├── Feature/                       # Tests de integración (Pest PHP)
│   │   ├── AppointmentTest.php        # Flujo C: Agendamiento
│   │   ├── StripePaymentTest.php      # Flujo B: Pagos y Webhook
│   │   ├── ChimeTest.php             # Videollamada (mock service)
│   │   └── Auth/                      # Flujo A: Login y Registro
│   └── e2e/                           # Tests E2E (Playwright)
│       ├── login.spec.js              # Flujo A: Registro, login, acceso protegido
│       ├── payment.spec.js            # Flujo B: Pagos y webhook Stripe
│       ├── appointment.spec.js        # Flujo C: Reserva, doble booking, cancelación
│       └── chime.spec.js             # Videollamada: permisos de cámara/micrófono
├── .github/workflows/
│   └── qa.yml                         # Pipeline de CI (GitHub Actions)
├── app/                               # Código Laravel (mock de la aplicación)
└── ...
```

---

## Requisitos

- **PHP** >= 8.2
- **Composer**
- **Node.js** >= 18
- **npm**

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd qa-case-opcionyo

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias Node
npm install

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Preparar base de datos y seeders
php artisan migrate:fresh --seed

# 6. Compilar frontend
npm run build

# 7. Instalar navegadores de Playwright
npx playwright install --with-deps chromium
```

---

## Ejecución de Tests

### Correr todo con un solo comando

```bash
# En Linux/macOS (bash):
php artisan migrate:fresh --seed --force && php artisan test && npx playwright test

# En Windows (PowerShell):
php artisan migrate:fresh --seed --force; php artisan test; npx playwright test
```

### Correr tests por separado

```bash
# Tests de integración (Pest/PHPUnit)
php artisan test

# Tests E2E (Playwright)
npx playwright test

# Tests E2E con interfaz visual
npx playwright test --headed

# Un flujo específico
npx playwright test tests/e2e/login.spec.js
npx playwright test tests/e2e/payment.spec.js
npx playwright test tests/e2e/appointment.spec.js
npx playwright test tests/e2e/chime.spec.js
```

---

## Pipeline de CI

El archivo `.github/workflows/qa.yml` ejecuta automáticamente:

1. **Job 1 — Laravel Tests:** Instala PHP, Composer, prepara la BD y ejecuta `php artisan test`.
2. **Job 2 — Playwright E2E:** Depende del Job 1. Instala Node, compila el frontend, y ejecuta `npx playwright test`.

El pipeline se dispara en cada **push** y **pull request** a `main`. Si algún test falla, el merge se bloquea.

---

## Documentación

| Documento | Contenido |
|:---|:---|
| [PROCESS.md](PROCESS.md) | Herramientas utilizadas, decisiones de diseño y uso de IA |
| [plan/PLAN.md](plan/PLAN.md) | Estrategia de QA, selección de herramientas y criterios de producción |
| [bugs/BUGS.md](bugs/BUGS.md) | 3 bugs críticos encontrados con pasos de reproducción y severidad |

---

## Stack

- **Backend:** Laravel 12 (PHP 8.4)
- **Frontend:** Blade + Alpine.js + Tailwind CSS
- **Tests de Integración:** Pest PHP
- **Tests E2E:** Playwright
- **Base de Datos:** SQLite
- **CI/CD:** GitHub Actions
