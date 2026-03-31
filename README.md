# Centtest

Sistema de gestión para centro de entrenamiento/capacitación desarrollado con Laravel 12.

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3 · Laravel 12 |
| Frontend | Blade · Tailwind CSS 3 · Alpine.js · Vite |
| Base de datos | MySQL 8.0 |
| Cache / Queues | Redis 7 |
| Servidor web | Nginx (Alpine) |
| Contenedores | Docker · Docker Compose |
| Autenticación | Laravel Breeze · Sanctum |
| Permisos | Spatie Laravel Permission |
| Exportación | Maatwebsite Excel · DomPDF · League CSV |
| Integraciones | Google API Client |

## Módulos principales

- **Inscripciones** — registro de participantes, asignaciones a programas y control de pagos
- **Programas y Módulos** — gestión de programas académicos y sus módulos de clase
- **Docentes** — ficha del docente y archivos adjuntos
- **Calificaciones** — notas, seguimientos y actas de graduación
- **Asistencia** — registro por clase/módulo
- **Solicitudes de Arte** — workflow de piezas gráficas con historial de modificaciones
- **Pilares de Contenido** — planificación de contenido para marketing
- **Documentos** — gestión y seguimiento de documentos con contactos
- **Gestión Financiera** — ingresos, egresos e inversiones
- **Equipos de Marketing** — miembros y asignaciones
- **Solicitudes de Pago** — generación y exportación de comprobantes

## Requisitos previos

- Docker y Docker Compose instalados
- (Desarrollo local) PHP 8.3, Composer, Node.js 20+

## Instalación y puesta en marcha

### Con Docker (recomendado)

```bash
# 1. Clonar el repositorio
git clone <url-del-repo> centtest
cd centtest

# 2. Copiar variables de entorno
cp .env.example .env

# 3. Editar .env con los valores reales (DB, APP_KEY, Google credentials, etc.)

# 4. Levantar los servicios
docker compose up -d

# 5. Ejecutar migraciones y seeders
docker compose exec app php artisan migrate --seed
```

La aplicación estará disponible en `http://localhost:8001`.  
phpMyAdmin (perfil `admin`): `http://localhost:8081`.

### Desarrollo local

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configurar DB en .env y luego:
php artisan migrate --seed

npm install
npm run dev

php artisan serve
```

## Variables de entorno relevantes

| Variable | Descripción |
|---|---|
| `APP_KEY` | Clave de cifrado de Laravel (generar con `php artisan key:generate`) |
| `DB_*` | Conexión a la base de datos principal |
| `DB_EXTERNAL_*` | Conexión a base de datos externa (opcional) |
| `GOOGLE_*` | Credenciales de Google API |
| `MAIL_*` | Configuración de correo saliente |
| `REDIS_*` | Conexión a Redis |

## Comandos útiles

```bash
# Limpiar caché
php artisan optimize:clear

# Ejecutar tests
php artisan test

# Generar exports con cola
php artisan queue:work

# Compilar assets para producción
npm run build
```

## Perfiles de Docker Compose

| Perfil | Servicio extra |
|---|---|
| `admin` | phpMyAdmin en el puerto 8081 |
| `cache` | Redis con persistencia |

```bash
docker compose --profile admin --profile cache up -d
```

## Estructura del proyecto

```
app/
├── Console/          # Comandos Artisan programados
├── Exports/          # Clases de exportación Excel/CSV
├── Helpers/          # Utilidades globales
├── Http/
│   ├── Controllers/  # Controladores
│   ├── Middleware/   # Middleware personalizado
│   └── Requests/     # Form Requests (validación)
├── Models/           # Modelos Eloquent
├── Notifications/    # Notificaciones
├── Observers/        # Observers de modelos
├── Policies/         # Políticas de autorización
├── Providers/        # Service Providers
├── Services/         # Lógica de negocio desacoplada
└── View/             # View Composers / Components
database/
├── migrations/       # Migraciones de base de datos
├── seeders/          # Seeders
└── factories/        # Factories para tests
resources/
├── views/            # Plantillas Blade
├── js/               # Alpine.js y scripts
└── css/              # Estilos con Tailwind
```

## Licencia

Uso interno — todos los derechos reservados.

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
