<h1 align="center">Jobify</h1>

## Descripción del proyecto
Jobify es una plataforma que conecta clientes con proveedores de servicios.
Permite publicar servicios, buscar por filtros, reservar, gestionar pagos y mantener comunicación entre usuarios.

El repositorio está dividido en frontend y backend, con despliegue containerizado mediante Docker Compose.

## Stack tecnológico
- Frontend: React + Vite + Tailwind
- Backend: PHP (arquitectura por rutas/controladores/modelos MVC + API REST) 
- Base de datos: MariaDB
- Servidor web: Nginx (frontend y backend)
- Infraestructura local: Docker Compose
- Testing backend: PHPUnit

## Arquitectura
La arquitectura actual en Docker utiliza servicios separados por puerto. 
En entornos de despliegue real se implementó un reverse proxy con Nginx.

- Frontend: expuesto en FRONTEND_PORT (host) hacia 80 (contenedor)
- Backend: expuesto en BACKEND_PORT (host) hacia 80 (contenedor)
- MariaDB: solo en red interna, sin puertos publicados al host

Redes Docker:
- public: exposición del frontend
- internal: comunicación interna backend + base de datos

Variables principales:
- FRONTEND_PORT
- BACKEND_PORT
- APP_URL
- API_URL
- CORS_ORIGIN
- MARIADB_HOST

## Tu rol en el proyecto
Fui designado como líder técnico del proyecto por mi experiencia previa, asumiendo la coordinación de decisiones de arquitectura, infraestructura y estrategia de desarrollo. Desde el inicio, definí un enfoque de trabajo ágil que permitiera cumplir con los requisitos académicos y, al mismo tiempo, sostener el avance del producto en paralelo con las responsabilidades estudiantiles del equipo.

En la fase de infraestructura, configuré el entorno sobre Proxmox con AlmaLinux (opción recomendada por la institución) y establecí el despliegue mediante Docker. Además, implementé manualmente Nginx como reverse proxy para integrar correctamente frontend y backend.

Dado que el servidor solo podía administrarse desde la red local institucional, diseñé una solución de acceso remoto sin apertura de puertos públicos, utilizando ngrok para tunelizar SSH (22) y HTTPS (443). Esto permitió administrar el servidor de forma remota (Nginx, Docker y configuración general) y publicar temporalmente la aplicación en Internet a través de una URL pública.

Para ofrecer una URL de dominio propia, integré Cloudflare y configuré un Worker que redirigía el dominio adquirido hacia la URL pública de ngrok de manera transparente para el usuario final.

En cuanto al desarrollo, comencé como programador principal mientras el resto del equipo se formaba técnicamente. Luego pasé a un rol de programador secundario para concentrarme en la operación de infraestructura y soporte técnico transversal.

Las decisiones tecnológicas se tomaron en función de los requisitos del proyecto: el backend se implementó en PHP (requisito obligatorio), mientras que para el frontend se eligió React por su madurez y adopción en la industria, complementado con Tailwind CSS para acelerar la implementación de interfaz y React Router para el enrutado SPA.

## Ejecución con Docker
1. Clonar repositorio y entrar al proyecto:

```bash
git clone <URL_DEL_REPO>
cd jobify
```

2. Crear archivo `.env` a partir de `.env.example` y completar valores.

```bash
cp .env.example .env
```

3. Levantar servicios:

```bash
docker compose up --build
```

4. Acceder a:
- Frontend: APP_URL
- Backend/API: API_URL

Valores locales por defecto de `.env.example`:
- Frontend: `http://localhost:8080`
- Backend/API: `http://localhost:8081`

### Si falta `.env`
El compose fallará de forma explícita indicando variables faltantes (por ejemplo `API_URL`, `MARIADB_PASSWORD`, `CORS_ORIGIN`).
La solución es crear `.env` desde `.env.example` y completar valores obligatorios.

## Sección de testing
Tests disponibles en backend con PHPUnit.

Ejecución recomendada:

```bash
cd backend
composer install
vendor/bin/phpunit
```

Notas:
- Algunos tests requieren configuración de entorno (BD y APIs externas).
- Cuando faltan credenciales o servicios externos, varios tests se marcan como skipped.
- Ver detalle en backend/tests/README.md.

## Sección de seguridad
Lineamientos aplicados:
- No se versiona archivo .env con secretos reales
- Uso de .env.example como plantilla de variables
- Sin contraseñas hardcodeadas en lógica de negocio crítica
- Sin exposición de puertos de base de datos al host
- Variables sensibles obligatorias en entorno (sin fallbacks inseguros)

Recomendaciones operativas:
- Usar contraseñas fuertes en MARIADB_PASSWORD, MYSQL_ROOT_PASSWORD y ADMIN_PASSWORD
- Definir KEY_HEX válida para cifrado estable
- Restringir CORS a dominios permitidos en producción
- Revisar periódicamente dependencias y versiones de imágenes
