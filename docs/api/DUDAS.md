# DUDAS — lo que no se pudo determinar o verificar

## Entorno de ejecución (limitación principal)

**No hay Docker en la máquina de documentación.** `docker compose up --build` no se pudo ejecutar:

```
$ docker compose up --build -d
/usr/bin/bash: línea 1: docker: orden no encontrada
```

Tampoco hay PHP, Composer ni MariaDB instalados, y `sudo` pide contraseña. Por eso el stack se levantó en espacio de usuario:

| Componente | Versión usada | Comando |
|------------|---------------|---------|
| PHP | 8.4.23 (static-php-cli, CLI con `pdo_mysql`, `curl`, `openssl`, `mbstring`) | `/tmp/opencode/php/php -S 127.0.0.1:8081 /tmp/opencode/harness/router.php` (cwd `backend/`) |
| MariaDB | 10.6.21 (misma serie que `MARIADB_VERSION=10.6`) | `mariadbd --port=3307 --bind-address=127.0.0.1 --character-set-server=utf8mb4` |
| Composer | 2.10.3 | `composer install --no-dev` en `backend/` |
| Aislamiento de red | `unshare -rn` (loopback only) | todo el stack y `curl` corren dentro del namespace |

Consecuencias:

- **No se verificaron los healthchecks de `docker-compose.yml`.** El criterio "esperá a que pasen los healthchecks" no se pudo cumplir.
- **No se ejecutó nginx.** `backend/nginx.conf` no está activo: no hay CORS, ni `X-Frame-Options`, ni `OPTIONS 204`, ni ocultamiento de `X-Powered-By`. Los headers CORS y el `204` de OPTIONS están marcados `[LEÍDO]` desde `nginx.conf`.
- El servidor usado es el built-in de PHP, no `php-fpm`; diferencias posibles en manejo de `$_FILES`, límites de subida y `$_SERVER`.
- `index.php` fija `display_errors=0`, así que los warnings quedaron ocultos; para diagnosticar fatales se leyó el log del servidor PHP.
- La base se creó con `character_set_server=utf8mb4` para imitar la imagen oficial MariaDB 10.6 (el tarball genérico arranca en `latin1`; con `latin1`, `DB.sql` falla al crear la tabla `Usuario` por la `ñ` de `contraseña`).

### Incidente de aislamiento (transparencia)

Antes de montar el namespace aislado, dos probes iniciales salieron a Internet con las claves **falsas** del `.env`:

- `GET /servicios/IA` llegó a `api.groq.com` y devolvió `Invalid API Key`.
- `POST /usuario/register` (proveedor) llegó a `commentanalyzer.googleapis.com` con `fake_google_api_key`.

No se usaron credenciales reales ni se produjeron efectos en servicios externos. Todos los resultados registrados en `golden-cases.json` se re-ejecutaron después dentro del namespace sin salida a Internet; las respuestas de error externo que se documentan son las del aislamiento (`Could not resolve host...`).

## Servicios externos: qué no se pudo observar

1. **Cloudinary — subida exitosa.** No se pudo verificar la URL transformada que devuelve `uploadFoto` en éxito (`1280x720`, `q_auto`, `f_auto` para servicios; `150x150` con face detection para perfiles; `800x800` limit-fit para reseñas). Con la clave falsa y sin red, la respuesta es un array de error y se documenta el fallo. `needs_mock: true`.
2. **Cloudinary — fallback de avatar.** El fallback DiceBear sí se ejecutó (no requiere red) y se verificó la URL guardada en `Usuario.imagen`.
3. **MercadoPago — `GET /pago/callback` exitoso (302).** Requiere un `code` OAuth real. No se observó el `Location` con tokens; se documenta `[LEÍDO]` desde `routes/Pago.php:180-186`.
4. **MercadoPago — creación de preferencia exitosa.** `GET /pago/mp` con tokens válidos no se pudo probar. Con tokens guardados y sin red, el SDK lanza una excepción no capturada → 500 vacío. Con un token inválido pero con red, el SDK lanzaría `MPApiException` y la ruta devolvería JSON 500 "Error al crear preferencia de pago"; **esa diferencia no se verificó**.
5. **MercadoPago — refund/capture exitosos.** `PUT /pago/cancelar` y `/aprobar` sin `paymentId` se probaron (actualización local). Con `paymentId` y sin red, el error es de conexión; con MP real el comportamiento depende de credenciales y del estado del pago. No verificado.
6. **Google Perspective — moderación.** No se pudo distinguir entre "clave inválida", "cuota" y "red caída": en todos los casos `IA::Moderacion` devuelve un string y el chequeo `isset($string['error'])` es siempre falso, por lo que el registro continúa. Tampoco se verificó el caso de contenido tóxico real (score ≥ 0.5). `needs_mock: true`.
7. **Groq — respuesta exitosa.** Solo se observó el error de red/key. El formato de `data.response` en éxito (`choices[0].message`) se deduce del código. `needs_mock: true`.
8. **Límite real de subida.** `ini_set('upload_max_filesize','20M')` no surte efecto (directiva `PHP_INI_PERDIR`). No se pudo determinar el límite efectivo del contenedor (`php.ini` de `php:8.4-fpm-alpine`); probablemente 2 MB por defecto.

## Comportamientos ambiguos o no determinables

9. **`GET /pago/callback` con configuración faltante.** `loginMP` devuelve `code` 500 y la ruta lo propaga; no se probó cada combinación (sin `CLIENT_ID`, sin `SECRET`, sin `REDIRECT_URI`).
10. **`GET /pago/mp` con `KEY_HEX` ausente.** El constructor genera una clave aleatoria; no se probó el ciclo completo guardar→leer→desencriptar. Se deduce que falla la desencriptación (clave distinta por request).
11. **`POST /pago/saveTokens` con `id_proveedor` inexistente.** El UPDATE afecta 0 filas y la ruta igual responde 200 `[]`; no se ejecutó contra un id inexistente, pero el código no verifica `rowCount`.
12. **`POST /usuario/register` con moderación aprobada.** No se pudo verificar que un score < 0.5 continúe el registro; se deduce de `IA::Moderacion`.
13. **`POST /servicios/crear` con Cloudinary OK.** Se deduce que guarda la URL transformada; no ejecutado.
14. **`GET /servicios/getServicio` sin `id` con catálogo vacío.** Se observó 500 "No se encontraron servicios" en el primer arranque (base vacía), antes de cargar el seed; no forma parte de `golden-cases.json` porque el seed siempre tiene servicios.
15. **Fechas y edad.** El cálculo de edad usa `DateTime::diff()->y`; el comportamiento exacto el día del cumpleaños y con zonas horarias no se probó.
16. **`Pagos.estado` nulo.** `POST /pago/pagar` inserta sin `estado`, que queda `NULL`. `verificarContratacion` solo cuenta `'completado'`, así que un pago recién insertado no cuenta. Verificado indirectamente; el valor `NULL` en el enum no se inspeccionó en la respuesta de historial (sí en la DB).
17. **`/admin/*` si se rutea.** Todo el comportamiento interno de `routes/Admin.php` es `[LEÍDO]`; solo se ejecutó el 404 actual. No se probó qué pasaría al agregar el `case 'admin'`.
18. **CORS real.** No verificable sin nginx. `nginx.conf` refleja cualquier `Origin` y permite credenciales; no se probó el preflight real.
19. **`OPTIONS` en PHP.** Se ejecutó y devolvió 200 vacío; con nginx debería ser 204. La discrepancia no se pudo observar en el stack real.
20. **Orden de dependencias del seed.** `golden-cases.json` asume que se carga `seed.sql` antes de correr los casos y que se ejecutan en el orden listado (varios casos crean filas). Re-ejecutar sin recargar el seed puede cambiar ids/duplicados.

## Decisiones de documentación

21. Los endpoints `/admin/*` se documentan con `x-routed: false` y `x-evidence: LEÍDO`, porque el contrato vigente es 404. Sus respuestas "si estuviera ruteado" están marcadas explícitamente `[LEÍDO, only if routed]` para no confundirlas con comportamiento actual.
22. Los ejemplos de request usan los nombres de campo reales del wire (`habilidades[]`, `categoria[]`, `fotoServicio[]`). En `x-www-form-urlencoded`/multipart, PHP los convierte en arrays bajo la clave sin corchetes.
23. En `openapi.yaml`, `Content-Type: application/json` se documenta en todas las respuestas, incluidos los 200 vacíos, porque `index.php` lo fija siempre. Los bodies vacíos se documentan con `content: {}`.
24. No se documentaron como operaciones separadas los paths comodín (`/pago/<acción>`, `/chat/<acción>`); su comportamiento de fallback está en `x-other-actions` / `x-unsupported-methods` de cada operación y en QUIRKS Q66-Q68.

25. **Propagación de variables de entorno en php-fpm.** `backend/src/DB.php` lee solo `$_ENV['MARIADB_*']`. En el harness funcionó porque el CLI tiene `variables_order=EGPCS`. No se pudo verificar que la imagen `php:8.4-fpm-alpine` con `clear_env` y `variables_order` del contenedor exponga esas variables en `$_ENV`; si no lo hace, `DB::connect()` fallaría en Docker aunque funcione localmente.
26. **Extensiones de la imagen Docker.** El `Dockerfile` solo instala `pdo` y `pdo_mysql`. No se pudo comprobar si `curl`, `openssl` y `mbstring` vienen habilitadas en `php:8.4-fpm-alpine`; el harness usa un PHP estático que sí las incluye. Si faltara `curl`, `IA::Curl` y los chequeos `hasCurlExtension()` de MercadoPago cambiarían de comportamiento.
