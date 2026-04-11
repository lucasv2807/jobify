# Arquitectura de despliegue

## Definicion actual
Este proyecto usa una arquitectura de servicios separados por puerto.
No se utiliza un reverse proxy unico que enrute frontend y backend desde una sola entrada.

## Exposicion de servicios
- Frontend (Nginx SPA): host `${FRONTEND_PORT}` -> contenedor `80`
- Backend (Nginx + PHP-FPM): host `${BACKEND_PORT}` -> contenedor `80`
- Base de datos (MariaDB): sin puertos publicados al host

## Redes Docker
- `public`: usada por el frontend para exponer la app al host
- `internal` (internal: true): backend y base de datos

## Flujo de red
1. El usuario entra al frontend por `APP_URL`.
2. El frontend consume la API en `API_URL` mediante `VITE_API_URL`.
3. El backend se conecta a MariaDB por red interna usando `MARIADB_HOST=mariadb`.

En entorno local, `.env.example` define por defecto:
- `APP_URL=http://localhost:8080`
- `API_URL=http://localhost:8081`

## Variables de entorno involucradas
- `FRONTEND_PORT`
- `BACKEND_PORT`
- `APP_URL`
- `API_URL`
- `CORS_ORIGIN`
- `MARIADB_HOST`

## Si mas adelante quieres un reverse proxy unico
Puedes agregar un servicio Nginx gateway que publique solo `80/443` y enrute:
- `/` -> frontend
- `/api` -> backend
En ese caso, frontend y backend pueden dejar de exponer puertos al host.
