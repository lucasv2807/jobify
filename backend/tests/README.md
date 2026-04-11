# Tests - Jobify Backend

## 📋 Descripción

Este directorio contiene los tests unitarios y de integración para el backend de Jobify. Utilizamos **PHPUnit** como framework de testing.

---

## 🚀 Instalación

Las dependencias de testing ya están incluidas en `composer.json`. Si no las tienes instaladas, ejecuta:

```bash
composer install
```

---

## ▶️ Ejecutar Tests

### Ejecutar todos los tests

```bash
composer test
```

O directamente con PHPUnit:

```bash
./vendor/bin/phpunit
```

### Ejecutar un test específico

```bash
composer run test:filter DBTest
```

O con PHPUnit:

```bash
./vendor/bin/phpunit --filter DBTest
```

### Ejecutar con reporte de cobertura

```bash
composer run test:coverage
```

Esto generará un reporte HTML en el directorio `coverage/`.

---

## 📁 Estructura de Tests

```
tests/
├── DBTest.php                   # Tests para la clase DB
├── ResponseTest.php             # Tests para la clase Response
├── BlackListTest.php            # Tests para la clase BlackList
├── IATest.php                   # Tests para la clase IA
├── ChatControladorTest.php      # Tests para el controlador Chat
├── PagoControladorTest.php      # Tests para el controlador Pago
├── ResenaControladorTest.php    # Tests para el controlador Resena
├── ReservaControladorTest.php   # Tests para el controlador Reserva
├── ServicioControladorTest.php  # Tests para el controlador Servicio
└── UsuarioControladorTest.php   # Tests para el controlador Usuario
```

---

## 🧪 Tests Disponibles

### DBTest.php

Tests para la clase `DB` que maneja la conexión a la base de datos:

| Test | Descripción |
|------|-------------|
| `testDBClassExists` | Verifica que la clase DB existe |
| `testConnectMethodExists` | Verifica que el método connect existe y es estático |
| `testConnectReturnsPDOInstance` | Verifica que connect retorna una instancia de PDO |
| `testConnectUsesSingletonPattern` | Verifica el patrón Singleton (misma instancia) |
| `testPDOErrorModeIsException` | Verifica que PDO usa ERRMODE_EXCEPTION |
| `testConnectsToCorrectDatabase` | Verifica conexión a la base de datos 'Jobify' |
| `testCanExecuteQueries` | Verifica que puede ejecutar queries SQL |
| `testDatabaseTablesExist` | Verifica que las tablas necesarias existen |
| `testPagosTableHasPaymentIdColumn` | Verifica que la tabla Pagos tiene la columna paymentId |
| `testThrowsExceptionWithInvalidCredentials` | Verifica manejo de errores |
| `testDBSQLFileExists` | Verifica que el archivo DB.sql existe |
| `testCanExecutePreparedStatements` | Verifica prepared statements |
| `testCanHandleTransactions` | Verifica manejo de transacciones |

### ResponseTest.php

Tests para la clase `Response` que maneja las respuestas de la API:

| Test | Descripción |
|------|-------------|
| `testResponseClassExists` | Verifica que la clase Response existe |
| `testSuccessMethodExists` | Verifica que el método success existe y es estático |
| `testErrorMethodExists` | Verifica que el método error existe y es estático |
| `testNotFoundMethodExists` | Verifica que el método notFound existe y es estático |
| `testSuccessWithDefaultParameters` | Verifica respuesta exitosa por defecto |
| `testSuccessWithCustomData` | Verifica respuesta con datos personalizados |
| `testSuccessWithEmptyArray` | Verifica respuesta con array vacío |
| `testSuccessWithComplexData` | Verifica respuesta con datos complejos |
| `testErrorWithDefaultParameters` | Verifica error por defecto |
| `testErrorWithCustomMessage` | Verifica error con mensaje personalizado |
| `testErrorWithErrorDetail` | Verifica error con detalle adicional |
| `testErrorWithoutErrorDetail` | Verifica error sin detalle |
| `testNotFound` | Verifica respuesta notFound |
| `testSuccessGeneratesValidJSON` | Verifica que genera JSON válido (success) |
| `testErrorGeneratesValidJSON` | Verifica que genera JSON válido (error) |
| `testNotFoundGeneratesValidJSON` | Verifica que genera JSON válido (notFound) |
| `testSuccessResponseStructure` | Verifica estructura de respuesta exitosa |
| `testErrorResponseStructure` | Verifica estructura de respuesta de error |
| `testNotFoundResponseStructure` | Verifica estructura de respuesta notFound |
| `testSuccessWithVariousHTTPCodes` | Verifica códigos HTTP comunes (success) |
| `testErrorWithVariousHTTPCodes` | Verifica códigos HTTP de error |
| `testSuccessWithSpecialCharacters` | Verifica manejo de caracteres especiales (success) |
| `testErrorWithSpecialCharacters` | Verifica manejo de caracteres especiales (error) |

### BlackListTest.php

Tests para la clase `BlackList` que maneja el filtrado de palabras prohibidas:

| Test | Descripción |
|------|-------------|
| `testBlackListClassExists` | Verifica que la clase BlackList existe |
| `testPublicMethodsExist` | Verifica que todos los métodos públicos existen |
| `testContienePalabrasProhibidasConTextoLimpio` | Verifica detección con texto limpio |
| `testDetectaPalabrasProhibidasSimples` | Verifica detección de palabras simples |
| `testDetectaPalabrasConAcentos` | Verifica detección de palabras con acentos |
| `testDetectaMultiplesPalabrasProhibidas` | Verifica detección de múltiples palabras |
| `testDetectaPalabrasEnMayusculas` | Verifica detección case-insensitive |
| `testDetectaPalabrasEnTextoMixto` | Verifica detección en texto mixto |
| `testManejaTextoVacio` | Verifica manejo de texto vacío |
| `testManejaNull` | Verifica manejo de null |
| `testManejaArrayDeStrings` | Verifica manejo de arrays |
| `testDetectaPalabrasEnArrays` | Verifica detección en arrays |
| `testValidarTextoConTextoLimpio` | Verifica validación de texto limpio |
| `testValidarTextoLanzaExcepcionConPalabrasProhibidas` | Verifica que lanza excepción |
| `testValidarTextoConArrayLimpio` | Verifica validación de arrays limpios |
| `testValidarTextoConArrayConPalabraProhibida` | Verifica validación de arrays con palabras prohibidas |
| `testCensurarTextoConTextoLimpio` | Verifica censura de texto limpio |
| `testCensurarTextoCensuraPalabras` | Verifica que censura palabras |
| `testCensurarTextoMantieneFormato` | Verifica que mantiene formato |
| `testCensurarTextoConMultiplesPalabras` | Verifica censura de múltiples palabras |
| `testCensurarTextoConArray` | Verifica censura de arrays |
| `testCensurarTextoConTextoVacio` | Verifica censura de texto vacío |
| `testCensurarTextoConNull` | Verifica censura de null |
| `testAgregarPalabra` | Verifica agregar nueva palabra |
| `testAgregarPalabraNoDuplicados` | Verifica que no agrega duplicados |
| `testAgregarPalabraNormalizaTexto` | Verifica normalización al agregar |
| `testObtenerListaCompleta` | Verifica obtención de lista |
| `testContarPalabras` | Verifica conteo de palabras |
| `testContarPalabrasCoincideConLista` | Verifica consistencia |
| `testDetectaPalabrasConEspacios` | Verifica palabras con espacios |
| `testNoDetectaPalabrasParciales` | Verifica límites de palabras |
| `testNormalizacionAcentos` | Verifica normalización de acentos |
| `testManejaTextoConSaltosDeLinea` | Verifica manejo de saltos de línea |
| `testManejaTextoConCaracteresEspeciales` | Verifica caracteres especiales |
| `testCensuradoMantieneLongitud` | Verifica longitud en censura |
| `testRendimientoConTextoLargo` | Verifica rendimiento |
| `testValidarTextoIncluyeNombreCampo` | Verifica mensaje de error |
| `testDeteccionCaseInsensitive` | Verifica todas las variaciones |
| `testListaNoEstaVacia` | Verifica que la lista tiene contenido |

### IATest.php

Tests para la clase `IA` que maneja integraciones con APIs de inteligencia artificial:

| Test | Descripción |
|------|-------------|
| `testIAClassExists` | Verifica que la clase IA existe |
| `testPublicMethodsExist` | Verifica que todos los métodos públicos existen |
| `testCurlManejaErroresDeConexion` | Verifica manejo de errores de conexión |
| `testCurlManejaJSONInvalido` | Verifica manejo de JSON inválido |
| `testCurlAceptaParametrosCorrectos` | Verifica parámetros del método Curl |
| `testCurlSinAPIKey` | Verifica Curl sin API key |
| `testModeracionSinAPIKey` | Verifica Moderacion sin API key |
| `testModeracionAceptaTexto` | Verifica parámetros de Moderacion |
| `testModeracionRetornaJSON` | Verifica que retorna JSON válido |
| `testModeracionTextoApropiado` | Verifica detección de texto apropiado |
| `testModeracionTextoInapropiado` | Verifica detección de texto tóxico |
| `testChatIASinAPIKey` | Verifica ChatIA sin API key |
| `testChatIAAceptaParametros` | Verifica parámetros de ChatIA |
| `testChatIAConArrayVacio` | Verifica manejo de array vacío |
| `testChatIARetornaRespuestaValida` | Verifica respuesta válida del chat |
| `testChatIAIncluyeURLEnRespuesta` | Verifica inclusión de URLs |
| `testCurlConDatosComplejos` | Verifica manejo de datos complejos |
| `testModeracionConTextoLargo` | Verifica moderación con texto largo |
| `testModeracionConTextoVacio` | Verifica moderación con texto vacío |
| `testChatIAConPreguntaSimple` | Verifica respuesta a pregunta simple |
| `testChatIAConMultiplesServicios` | Verifica manejo de múltiples servicios |
| `testChatIAUsaModeloCorrecto` | Verifica configuración del modelo |
| `testModeracionUsaUmbralCorrecto` | Verifica umbral de moderación (0.9) |
| `testTiposDeRetorno` | Verifica tipos de retorno correctos |
| `testModeracionConCaracteresEspeciales` | Verifica caracteres especiales (moderación) |
| `testChatIAConCaracteresEspeciales` | Verifica caracteres especiales (chat) |
| `testClaseEsStateless` | Verifica que la clase no tiene estado |

**Nota:** Varios tests requieren API keys configuradas (`GOOGLE_API_KEY`, `GROQ_API_KEY`). Si no están disponibles, los tests se saltarán automáticamente.

### ChatControladorTest.php

Tests para el controlador `ChatControlador` que maneja el sistema de mensajería:

| Test | Descripción |
|------|-------------|
| `testChatControladorClassExists` | Verifica que la clase ChatControlador existe |
| `testPublicMethodsExist` | Verifica que todos los métodos públicos existen y son estáticos |
| `testSendMessageParameters` | Verifica parámetros del método sendMessage |
| `testSendMessageRechazaMensajeVacio` | Verifica rechazo de mensajes vacíos |
| `testSendMessageRechazaClienteInexistente` | Verifica validación de cliente existente |
| `testSendMessageConDatosValidos` | Verifica envío con datos válidos |
| `testCreateChatParameters` | Verifica parámetros del método createChat |
| `testCreateChatConParametrosValidos` | Verifica creación de chat |
| `testGetMessageHistoryParameters` | Verifica parámetros del método getMessageHistory |
| `testGetMessageHistoryChatNoExiste` | Verifica error cuando chat no existe |
| `testGetMessageHistoryValidaPermisosProveedor` | Verifica control de acceso del proveedor |
| `testGetMessageHistoryEstructuraCorrecta` | Verifica estructura de respuesta |
| `testGetChatsClienteParameters` | Verifica parámetros del método getChatsCliente |
| `testGetChatsClienteRetornaArray` | Verifica que retorna array |
| `testGetChatsClienteAgregaTipoChat` | Verifica que agrega campo 'tipo' => 'Chat' |
| `testGetChatsProveedorParameters` | Verifica parámetros del método getChatsProveedor |
| `testGetChatsProveedorRetornaArray` | Verifica que retorna array |
| `testCheckChatExistsParameters` | Verifica parámetros del método checkChatExists |
| `testCheckChatExistsRetornaValorValido` | Verifica tipo de retorno (booleano o array) |
| `testCheckChatExistsConIDsInvalidos` | Verifica comportamiento con IDs inválidos |
| `testSendMessageConMensajeLargo` | Verifica manejo de mensajes largos |
| `testSendMessageConCaracteresEspeciales` | Verifica manejo de caracteres especiales y emojis |
| `testGetChatsClienteSinChats` | Verifica manejo de clientes sin chats |
| `testGetChatsProveedorSinChats` | Verifica manejo de proveedores sin chats |
| `testMetodosManejanNullCorrectamente` | Verifica que no hay errores fatales con null |
| `testEstructuraDeErrorConsistente` | Verifica formato consistente de errores |
| `testGetMessageHistoryErrorDeAcceso` | Verifica error de acceso no autorizado |
| `testClaseUsaDependenciasCorrectamente` | Verifica carga de ChatModelo y UsuarioModelo |
| `testMetodosSonPublicosYEstaticos` | Verifica visibilidad y naturaleza estática |

**Nota:** Estos tests requieren datos válidos en la base de datos (cliente, servicio, proveedor). Algunos tests se saltarán si no hay datos disponibles.

### PagoControladorTest.php

Tests para el controlador `PagoControlador` que maneja pagos y la integración con MercadoPago:

| Test | Descripción |
|------|-------------|
| `testPagoControladorClassExists` | Verifica que la clase PagoControlador existe |
| `testConstructorInicializaClave` | Verifica inicialización del constructor |
| `testConstructorManejaKeyHexAusente` | Verifica manejo de KEY_HEX ausente |
| `testPublicMethodsExist` | Verifica que todos los métodos públicos existen |
| `testStaticMethodsExist` | Verifica métodos estáticos (refreshToken, guardarTokensMP) |
| `testPagarParameters` | Verifica parámetros del método pagar |
| `testPagarRetornaEstructuraValida` | Verifica estructura de retorno de pagar |
| `testHistorialPagosParameters` | Verifica parámetros del método historialPagos |
| `testHistorialPagosRetornaArray` | Verifica que retorna array |
| `testHistorialPagosManejaErrores` | Verifica manejo de errores en historial |
| `testHistorialPagosProveedorParameters` | Verifica parámetros para historial de proveedor |
| `testHistorialPagosProveedorRetornaArray` | Verifica retorno de array |
| `testObtenerPreferenciaMPParameters` | Verifica parámetros de obtenerPreferenciaMP |
| `testObtenerPreferenciaMPRechazaProveedorSinTokens` | Verifica rechazo sin tokens MP |
| `testObtenerPreferenciaMPEstructuraCorrecta` | Verifica estructura con preference_id y public_key |
| `testLoginMPParameters` | Verifica parámetros del método loginMP |
| `testLoginMPManejaCodigoInvalido` | Verifica manejo de código OAuth inválido |
| `testRefreshTokenParameters` | Verifica parámetros del método refreshToken |
| `testRefreshTokenManejaTokenInvalido` | Verifica manejo de refresh token inválido |
| `testCancelarCompraParameters` | Verifica parámetros del método cancelarCompra |
| `testCancelarCompraRechazaPagoInexistente` | Verifica rechazo de pago no encontrado |
| `testCancelarCompraEstructuraDeRespuesta` | Verifica estructura de respuesta (success/error) |
| `testAprobarPagoParameters` | Verifica parámetros del método aprobarPago |
| `testAprobarPagoRechazaPagoInexistente` | Verifica rechazo de pago no encontrado |
| `testAprobarPagoEstructuraDeRespuesta` | Verifica estructura de respuesta |
| `testGuardarTokensMPParameters` | Verifica parámetros del método guardarTokensMP |
| `testGuardarTokensMPPublicKeyOpcional` | Verifica que publicKey es opcional |
| `testDesencriptarTokenManejaToken` | Verifica desencriptación de tokens |
| `testClaseUsaPagoModelo` | Verifica carga de PagoModelo |
| `testLoginMPEstructuraDeError` | Verifica estructura de error de loginMP |
| `testRefreshTokenEsEstatico` | Verifica naturaleza estática de refreshToken |
| `testGuardarTokensMPEsEstatico` | Verifica naturaleza estática de guardarTokensMP |
| `testObtenerPreferenciaMPConPrecioNegativo` | Verifica manejo de precio negativo |
| `testObtenerPreferenciaMPConTituloVacio` | Verifica manejo de título vacío |
| `testHistorialPagosClienteSinPagos` | Verifica cliente sin pagos |
| `testHistorialPagosProveedorSinPagos` | Verifica proveedor sin pagos |
| `testCancelarCompraNoPermiteCancelarPagoCancelado` | Verifica prevención de doble cancelación |
| `testAprobarPagoNoPermiteAprobarPagoAprobado` | Verifica prevención de doble aprobación |
| `testAprobarPagoNoPermiteAprobarPagoCancelado` | Verifica rechazo de aprobar pago cancelado |
| `testEstructuraDeErrorConsistente` | Verifica formato consistente de errores |
| `testMetodosManejanNullCorrectamente` | Verifica manejo de valores null |
| `testLoginMPManejaRespuestaVacia` | Verifica manejo de respuesta vacía |
| `testRefreshTokenManejaTokenVacio` | Verifica manejo de token vacío |
| `testPagarValidaParametros` | Verifica validación de parámetros |
| `testDesencriptarTokenRetornaNullConTokenInvalido` | Verifica retorno null con token inválido |
| `testClaseTieneDependenciasCorrectas` | Verifica carga de PagoModelo e IA |
| `testMetodosNoSonFinales` | Verifica que métodos pueden ser sobrescritos |

**Nota:** Estos tests requieren conexión a BD y algunos requieren credenciales válidas de MercadoPago. Los tests se saltarán automáticamente si no están disponibles.

### ResenaControladorTest.php

Tests para el controlador `resenaControlador` que maneja reseñas con integración a Cloudinary:

| Test | Descripción |
|------|-------------|
| `testResenaControladorClassExists` | Verifica que la clase resenaControlador existe |
| `testPublicMethodsExist` | Verifica que todos los métodos públicos existen y son estáticos |
| `testCrearresenaParameters` | Verifica parámetros del método crearresena |
| `testCrearresenaRechazaCalificacionMenorA1` | Verifica rechazo de calificación < 1 |
| `testCrearresenaRechazaCalificacionMayorA5` | Verifica rechazo de calificación > 5 |
| `testCrearresenaAceptaCalificacion1` | Verifica aceptación de calificación 1 |
| `testCrearresenaAceptaCalificacion5` | Verifica aceptación de calificación 5 |
| `testCrearresenaRechazaIdClienteVacio` | Verifica validación de idCliente obligatorio |
| `testCrearresenaRechazaIdServicioVacio` | Verifica validación de idServicio obligatorio |
| `testCrearresenaRechazaCalificacionVacia` | Verifica validación de calificación obligatoria |
| `testCrearresenaManejaErrorSubidaImagen` | Verifica manejo de errores de upload |
| `testCrearresenaManejaImagenNull` | Verifica manejo de imagen null |
| `testCrearresenaManejaImagenNoArray` | Verifica validación de formato de imagen |
| `testCrearresenaManejaCloudinaryUrlAusente` | Verifica manejo sin CLOUDINARY_URL |
| `testGetresenaParameters` | Verifica parámetros del método getresena |
| `testGetresenaRetornaArray` | Verifica que retorna array |
| `testGetresenaManejaServicioInexistente` | Verifica manejo de servicio no encontrado |
| `testGetresenaManejaExcepciones` | Verifica captura de excepciones |
| `testCrearresenaAceptaComentarioVacio` | Verifica que comentario vacío es válido |
| `testCrearresenaAceptaComentarioLargo` | Verifica manejo de comentarios largos |
| `testCrearresenaAceptaCaracteresEspeciales` | Verifica manejo de ñ, acentos, emojis |
| `testCrearresenaRetornaIdEnCasoDeExito` | Verifica estructura de respuesta exitosa |
| `testClaseUsaResenaModelo` | Verifica carga de resenaModelo |
| `testCrearresenaManejaUploadErrors` | Verifica todos los códigos de error de upload |
| `testTodosLosMetodosSonEstaticos` | Verifica naturaleza estática de métodos |
| `testCrearresenaValidaCalificacionesDecimales` | Verifica aceptación de calificaciones decimales (3.5) |
| `testEstructuraDeErrorConsistente` | Verifica formato consistente de errores |
| `testCrearresenaCapturaExcepciones` | Verifica captura de excepciones en crearresena |
| `testGetresenaCapturaExcepciones` | Verifica captura de excepciones en getresena |
| `testCrearresenaValidaRangosDeCalificacion` | Verifica todos los rangos válidos (1-5) e inválidos |
| `testClaseUsaCloudinary` | Verifica carga de librería Cloudinary |
| `testCrearresenaOrdenDeValidaciones` | Verifica orden correcto de validaciones |

**Nota:** Estos tests requieren CLOUDINARY_URL configurada para subida de imágenes. Los tests se saltarán automáticamente si no está disponible.

### ReservaControladorTest.php

Tests para el controlador `ReservaControlador` que maneja la gestión de reservas:

| Test | Descripción |
|------|-------------|
| `testReservaControladorClassExists` | Verifica que la clase ReservaControlador existe |
| `testConstructorFuncionaCorrectamente` | Verifica funcionamiento del constructor |
| `testPublicMethodsExist` | Verifica que todos los métodos públicos existen |
| `testCrearReservaParameters` | Verifica parámetros del método crearReserva |
| `testCrearReservaRechazaIdClienteVacio` | Verifica validación de idCliente obligatorio |
| `testCrearReservaRechazaIdServicioVacio` | Verifica validación de idServicio obligatorio |
| `testCrearReservaRechazaFechaVacia` | Verifica validación de fecha obligatoria |
| `testCrearReservaRechazaTodosDatosVacios` | Verifica rechazo con todos los datos vacíos |
| `testCrearReservaConDatosValidos` | Verifica creación con datos válidos |
| `testCrearReservaCreaChat` | Verifica creación automática de chat |
| `testConfirmarReservaParameters` | Verifica parámetros del método confirmarReserva |
| `testConfirmarReservaConDatosValidos` | Verifica confirmación con datos válidos |
| `testConfirmarReservaAceptaConfirmadoCero` | Verifica aceptación de confirmado = 0 |
| `testConfirmarReservaAceptaConfirmadoUno` | Verifica aceptación de confirmado = 1 |
| `testConfirmarReservaManejaReservaInexistente` | Verifica manejo de reserva no encontrada |
| `testGetReservaProveedorParameters` | Verifica parámetros del método getReservaProveedor |
| `testGetReservaProveedorRetornaArray` | Verifica que retorna array |
| `testGetReservaProveedorAgregaTipoSchedule` | Verifica que agrega 'tipo' => 'schedule' |
| `testGetReservaProveedorSinReservas` | Verifica manejo de proveedor sin reservas |
| `testGetReservaClienteParameters` | Verifica parámetros del método getReservaCliente |
| `testGetReservaClienteRetornaArray` | Verifica que retorna array |
| `testGetReservaClienteAgregaTipoSchedule` | Verifica que agrega 'tipo' => 'schedule' |
| `testGetReservaClienteSinReservas` | Verifica manejo de cliente sin reservas |
| `testClaseUsaReservaModelo` | Verifica carga de ReservaModelo |
| `testClaseUsaChatControlador` | Verifica carga de ChatControlador |
| `testClaseUsaServicioControlador` | Verifica carga de ServicioControlador |
| `testCrearReservaAceptaFechaValida` | Verifica múltiples formatos de fecha |
| `testCrearReservaEstructuraDeRespuesta` | Verifica estructura de respuesta (result/error) |
| `testConfirmarReservaEstructuraDeRespuesta` | Verifica estructura de respuesta (success/error) |
| `testCrearReservaManejaNull` | Verifica manejo de null en parámetros |
| `testConfirmarReservaManejaNull` | Verifica manejo de null en confirmarReserva |
| `testGetReservaProveedorManejaNull` | Verifica manejo de null en getReservaProveedor |
| `testGetReservaClienteManejaNull` | Verifica manejo de null en getReservaCliente |
| `testEstructuraDeErrorConsistente` | Verifica formato consistente de errores |
| `testTodosLosMetodosSonPublicos` | Verifica visibilidad pública |
| `testNingunMetodoEsEstatico` | Verifica que métodos no son estáticos |
| `testCrearReservaConIdClienteCero` | Verifica rechazo de ID = 0 (cliente) |
| `testCrearReservaConIdServicioCero` | Verifica rechazo de ID = 0 (servicio) |
| `testGetReservaProveedorConIdInvalido` | Verifica manejo de proveedor inválido |
| `testGetReservaClienteConIdInvalido` | Verifica manejo de cliente inválido |
| `testConfirmarReservaSuccessEsBooleano` | Verifica tipo de dato en success |
| `testGetReservaProveedorArrayMapFunciona` | Verifica transformación con array_map |
| `testGetReservaClienteArrayMapFunciona` | Verifica transformación con array_map |
| `testCrearReservaRetornaResultEnExito` | Verifica key 'result' en respuesta exitosa |

**Nota:** Estos tests requieren datos válidos en BD (cliente, servicio, proveedor, reserva). Los tests se saltarán si no están disponibles.

### ServicioControladorTest.php

Tests para el controlador `ServicioControlador` que maneja servicios con Cloudinary e IA:

| Test | Descripción |
|------|-------------|
| `testServicioControladorClassExists` | Verifica que la clase ServicioControlador existe |
| `testPublicStaticMethodsExist` | Verifica que todos los métodos públicos son estáticos |
| `testCrearParameters` | Verifica parámetros del método crear (8 parámetros) |
| `testCrearRechazaPrecioNegativo` | Verifica rechazo de precio < 0 |
| `testCrearRechazaPrecioMuyAlto` | Verifica rechazo de precio > 500000 |
| `testCrearAceptaPrecioCero` | Verifica aceptación de precio = 0 |
| `testCrearAceptaPrecioLimite` | Verifica aceptación de precio = 500000 |
| `testCrearValidaProveedorExistente` | Verifica validación de proveedor |
| `testGetServicioParameters` | Verifica parámetros del método getServicio (ID opcional) |
| `testGetServicioConId` | Verifica obtención de servicio específico |
| `testGetServicioSinId` | Verifica obtención de todos los servicios |
| `testGetServicioConIdInexistente` | Verifica manejo de servicio no encontrado |
| `testBorrarParameters` | Verifica parámetros del método borrar |
| `testBorrarValidaProveedor` | Verifica que solo proveedores pueden borrar |
| `testSearchParameters` | Verifica parámetros del método search |
| `testSearchRetornaArray` | Verifica que search retorna array |
| `testSearchSinResultados` | Verifica mensaje cuando no hay resultados |
| `testUpdateServicioParameters` | Verifica 9 parámetros (todos opcionales excepto ID) |
| `testUpdateServicioRechazaPrecioNegativo` | Verifica validación de precio en actualización |
| `testUpdateServicioRechazaPrecioMuyAlto` | Verifica límite de precio en actualización |
| `testUpdateServicioValidaServicioExistente` | Verifica que servicio existe antes de actualizar |
| `testSearchByIAParameters` | Verifica parámetros del método searchByIA |
| `testSearchByIARequiereAPIKeys` | Verifica que requiere GROQ_API_KEY |
| `testGetServiciosByProveedorParameters` | Verifica parámetros del método getServiciosByProveedor |
| `testGetServiciosByProveedorRetornaArray` | Verifica retorno de array |
| `testGetServiciosByProveedorSinServicios` | Verifica proveedor sin servicios |
| `testSaveFavoritoParameters` | Verifica parámetros del método saveFavorito |
| `testSaveFavoritoDetectaDuplicados` | Verifica prevención de duplicados |
| `testDeleteFavoritoParameters` | Verifica parámetros del método deleteFavorito |
| `testDeleteFavoritoValidaExistencia` | Verifica que favorito existe antes de eliminar |
| `testGetFavoritosByClienteParameters` | Verifica parámetros del método getFavoritosByCliente |
| `testGetFavoritosByClienteValidaCliente` | Verifica que cliente existe |
| `testGetFavoritosByClienteValidaTipoUsuario` | Verifica que usuario es cliente (no proveedor) |
| `testClaseUsaServicioModelo` | Verifica carga de ServicioModelo |
| `testClaseUsaUsuarioModelo` | Verifica carga de UsuarioModelo |
| `testClaseUsaIA` | Verifica carga de clase IA |
| `testClaseUsaCloudinary` | Verifica carga de Cloudinary |
| `testTodosLosMetodosSonEstaticos` | Verifica naturaleza estática de todos los métodos |
| `testCrearManejaMultiplesFotos` | Verifica subida de múltiples imágenes |
| `testGetServicioIncluyeCalificacion` | Verifica cálculo de calificación promedio |
| `testSearchEstructuraDeRespuesta` | Verifica estructura completa de resultados |
| `testGetFavoritosByClienteIncluyeIsFavorito` | Verifica campo isFavorito = true |
| `testCrearCapturaExcepciones` | Verifica captura de excepciones |
| `testEstructuraDeErrorConsistente` | Verifica formato consistente de errores |
| `testUpdateServicioRetornaSuccess` | Verifica key 'success' en actualización exitosa |

**Nota:** Estos tests requieren CLOUDINARY_URL, GOOGLE_API_KEY (moderación), GROQ_API_KEY (búsqueda IA). Los tests se saltarán si no están disponibles.

### UsuarioControladorTest.php

Tests para el controlador `UsuarioControlador` que maneja registro, autenticación y perfil de usuarios:

| Test | Descripción |
|------|-------------|
| `testUsuarioControladorClassExists` | Verifica que la clase UsuarioControlador existe |
| `testPublicStaticMethodsExist` | Verifica que todos los métodos públicos son estáticos |
| `testRegisterParameters` | Verifica 13 parámetros del método register |
| `testRegisterRechazaEdadMenorA18` | Verifica rechazo de edad < 18 años |
| `testRegisterRechazaEdadMayorA100` | Verifica rechazo de edad > 100 años |
| `testRegisterAceptaEdad18` | Verifica aceptación de edad = 18 años |
| `testRegisterValidaExperienciaVsEdad` | Verifica coherencia: experiencia ≤ (edad - 18) |
| `testRegisterRechazaExperienciaNegativa` | Verifica rechazo de experiencia < 0 |
| `testRegisterEncriptaContrasena` | Verifica encriptación con password_hash |
| `testRegisterCreaFotoPerfilPorDefecto` | Verifica generación de foto con DiceBear si no se proporciona |
| `testLoginParameters` | Verifica parámetros del método login (email, password) |
| `testLoginRechazaUsuarioInexistente` | Verifica manejo de email no registrado |
| `testLoginRechazaContrasenaIncorrecta` | Verifica validación con password_verify |
| `testLoginEstructuraRespuestaExitosa` | Verifica keys: id, nombre, foto_perfil |
| `testGetUserParameters` | Verifica parámetro del método getUser (id opcional) |
| `testGetUserRechazaIdNull` | Verifica rechazo cuando ID no se proporciona |
| `testGetUserRetornaUsuarioNormal` | Verifica estructura para cliente (no proveedor) |
| `testGetUserRetornaProveedor` | Verifica keys adicionales para proveedor |
| `testGetUserManejaUsuarioInexistente` | Verifica manejo de usuario no encontrado |
| `testUpdateProfileParameters` | Verifica 12 parámetros del método updateProfile |
| `testUpdateProfileValidaUsuarioExistente` | Verifica que usuario existe antes de actualizar |
| `testUpdateProfileValidaExperienciaVsEdad` | Verifica coherencia experiencia/edad en actualización |
| `testUpdateProfileRechazaExperienciaNegativa` | Verifica validación de experiencia en actualización |
| `testUpdateProfileEstructuraRespuestaExitosa` | Verifica keys: success, mensaje |
| `testUpdatePasswordParameters` | Verifica 3 parámetros (idUsuario, passwordActual, nuevoPassword) |
| `testUpdatePasswordValidaUsuarioExistente` | Verifica que usuario existe |
| `testUpdatePasswordValidaContrasenaActual` | Verifica contraseña actual con password_verify |
| `testUpdatePasswordEstructuraRespuestaExitosa` | Verifica estructura de respuesta exitosa |
| `testClaseUsaUsuarioModelo` | Verifica carga de UsuarioModelo |
| `testClaseUsaIA` | Verifica carga de clase IA |
| `testClaseUsaCloudinary` | Verifica carga de Cloudinary |
| `testTodosLosMetodosSonEstaticos` | Verifica naturaleza estática de métodos |
| `testRegisterDiferenciaProveedorYCliente` | Verifica campos diferentes según tipo de usuario |
| `testRegisterManejaFormatosEsProveedor` | Verifica formatos: 'true', true, 1, '1' |
| `testGetUserIncluyeRegistroMp` | Verifica campo registroMp para proveedores |
| `testUpdateProfileActualizaHabilidades` | Verifica actualización de array habilidades |
| `testUpdateProfileActualizaTitulos` | Verifica actualización de array titulos |
| `testRegisterCapturaExcepciones` | Verifica captura de excepciones |
| `testEstructuraDeErrorConsistente` | Verifica formato consistente de errores |
| `testUpdateProfileSinFotoPerfil` | Verifica que foto_perfil es opcional |
| `testUpdatePasswordEncriptaNuevaContrasena` | Verifica encriptación de nueva contraseña |

**Nota:** Estos tests requieren conexión a BD, CLOUDINARY_URL (foto de perfil con transformación 150x150, face detection), y DiceBear API (foto por defecto). Los tests se saltarán si no están disponibles.

---

## ⚙️ Configuración

### Variables de Entorno

Los tests requieren las siguientes variables de entorno (desde `.env`):

```env
# Base de Datos (requerido para DBTest)
MARIADB_HOST=mariadb
MARIADB_USERNAME=jobify
MARIADB_PASSWORD=tu_password
MARIADB_PORT=3306

# APIs de IA (opcional para IATest - tests se saltarán si no están)
GOOGLE_API_KEY=tu_google_api_key
GROQ_API_KEY=tu_groq_api_key
```

Si no existe el archivo `.env`, los tests usarán valores por defecto.

### Base de Datos de Testing

Los tests se ejecutan contra la base de datos **Jobify**. Asegúrate de:

1. ✅ Tener MariaDB/MySQL corriendo
2. ✅ Las credenciales correctas en `.env`
3. ✅ El archivo `src/DB.sql` debe existir
4. ✅ (Opcional) El archivo `src/DB_UPDATE.sql` para actualizaciones

---

## 📊 Salida de Tests

### Éxito ✅

```
PHPUnit 12.x by Sebastian Bergmann and contributors.

Runtime:       PHP 8.x

.............                                                     13 / 13 (100%)

Time: 00:00.234, Memory: 8.00 MB

OK (13 tests, 25 assertions)
```

### Con Warnings ⚠️

Si algunos tests se saltan (por ejemplo, si no hay conexión a BD):

```
PHPUnit 12.x by Sebastian Bergmann and contributors.

.......S.....                                                     13 / 13 (100%)

Time: 00:00.123, Memory: 8.00 MB

OK, but incomplete, skipped, or risky tests!
Tests: 13, Assertions: 20, Skipped: 1.
```

### Con Errores ❌

```
PHPUnit 12.x by Sebastian Bergmann and contributors.

..F..........                                                     13 / 13 (100%)

Time: 00:00.234, Memory: 8.00 MB

FAILURES!
Tests: 13, Assertions: 22, Failures: 1.
```

---

## 🛠️ Troubleshooting

### "No se pudo conectar a la base de datos"

**Problema:** Los tests se saltan porque no pueden conectar a MariaDB.

**Solución:**

1. Verifica que MariaDB esté corriendo
2. Verifica las credenciales en `.env`
3. Verifica que puedas conectarte manualmente:

   ```bash
    mysql -u jobify -p -h mariadb -P 3306
   ```

### "El archivo DB.sql no existe"

**Problema:** No se encuentra el archivo de creación de la base de datos.

**Solución:**

1. Verifica que existe `src/DB.sql`
2. Verifica los permisos de lectura del archivo

### Tests fallan con errores de sintaxis

**Problema:** Incompatibilidad de versión de PHP.

**Solución:**

1. Asegúrate de usar PHP 8.0 o superior
2. Ejecuta: `php -v` para verificar la versión

---

## ✍️ Escribir Nuevos Tests

### Estructura básica de un test

```php
<?php

use PHPUnit\Framework\TestCase;

class MiClaseTest extends TestCase
{
    /**
     * Setup antes de cada test
     */
    protected function setUp(): void
    {
        // Preparación
    }

    /**
     * Test: Descripción del test
     */
    public function testAlgo(): void
    {
        // Arrange (Preparar)
        $valor = 5;
        
        // Act (Actuar)
        $resultado = $valor + 3;
        
        // Assert (Verificar)
        $this->assertEquals(8, $resultado);
    }
}
```

### Assertions comunes

```php
$this->assertTrue($condicion);
$this->assertFalse($condicion);
$this->assertEquals($esperado, $actual);
$this->assertSame($esperado, $actual);
$this->assertNull($valor);
$this->assertNotNull($valor);
$this->assertInstanceOf(Clase::class, $objeto);
$this->assertCount(5, $array);
$this->assertContains('texto', $array);
```

---

## 📚 Recursos

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPUnit Assertions](https://phpunit.de/manual/current/en/assertions.html)
- [Test-Driven Development (TDD)](https://en.wikipedia.org/wiki/Test-driven_development)

---

## 🎯 Mejores Prácticas

1. ✅ **Un test, una cosa**: Cada test debe verificar una sola funcionalidad
2. ✅ **Tests independientes**: Los tests no deben depender entre sí
3. ✅ **Nombres descriptivos**: El nombre del test debe explicar qué verifica
4. ✅ **AAA Pattern**: Arrange (preparar), Act (actuar), Assert (verificar)
5. ✅ **Cleanup**: Usa `setUp()` y `tearDown()` para preparar y limpiar
6. ✅ **Tests rápidos**: Los tests unitarios deben ser rápidos
7. ✅ **Tests deterministas**: Mismo resultado siempre con las mismas condiciones

---

## 📈 Cobertura de Código

Para generar un reporte de cobertura:

```bash
composer run test:coverage
```

Abre `coverage/index.html` en tu navegador para ver el reporte visual.

**Objetivo:** Mantener una cobertura de al menos 80% del código.

---

## 🤝 Contribuir

Al agregar nueva funcionalidad, **siempre agrega tests**:

1. Crea un nuevo archivo `tests/MiClaseTest.php`
2. Escribe los tests antes de la implementación (TDD)
3. Ejecuta los tests: `composer test`
4. Verifica la cobertura: `composer run test:coverage`
5. Asegúrate de que todos los tests pasen antes de hacer commit

---

## ✅ Checklist Pre-Commit

Antes de hacer commit, ejecuta:

```bash
# 1. Linting
composer run lint:check

# 2. Tests
composer test

# 3. Verificar cobertura
composer run test:coverage
```

Si todo pasa ✅, ¡estás listo para hacer commit!
