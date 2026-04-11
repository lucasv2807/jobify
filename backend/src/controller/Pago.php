<?php

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

require_once 'src/utils/IA.php';
require_once 'src/model/Pago.php';
require_once 'src/controller/Reserva.php';

class PagoControlador
{
    private $key;
    private $metodo = 'aes-256-gcm';

    private static function getEnvVar(string $name): ?string
    {
        $value = getenv($name);
        if ($value !== false) {
            return $value;
        }

        if (isset($_ENV[$name])) {
            return $value = $_ENV[$name];
        }

        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return null;
    }

    private static function hasCurlExtension(): bool
    {
        return function_exists('curl_init')
            && function_exists('curl_setopt')
            && function_exists('curl_exec')
            && function_exists('curl_close');
    }

    public function __construct()
    {
        // Property default values cannot contain function calls.
        // Initialize the key at runtime in the constructor instead.
        // Intentar obtener KEY_HEX de $_ENV primero (Dotenv), luego getenv()
        $hex = $_ENV['KEY_HEX'] ?? getenv('KEY_HEX');
        if ($hex === false || empty($hex)) {
            // Generar una clave por defecto para desarrollo (32 bytes para AES-256)
            // IMPORTANTE: En producción, debes configurar KEY_HEX en las variables de entorno
            $this->key = openssl_random_pseudo_bytes(32);
            error_log('WARNING: KEY_HEX no está configurada. Usando clave temporal. Configura KEY_HEX en producción.');
        } else {
            $this->key = @hex2bin($hex);
            if ($this->key === false) {
                error_log('ERROR: KEY_HEX no es un valor hexadecimal válido');
                $this->key = openssl_random_pseudo_bytes(32);
            }
        }
    }

    private static function mp($precio, $titulo, $tokenProvedor)
    {
        $frontendUrl = self::getEnvVar('FRONTEND_URL');
        if (empty($frontendUrl)) {
            return ['error' => 'FRONTEND_URL no está configurada en las variables de entorno.'];
        }
        
        $back_urls = [
            'success' => $frontendUrl . '/payment-success',
            'failure' => $frontendUrl . '/payment-failure',
            'pending' => $frontendUrl . '/payment-pending',
        ];
        MercadoPagoConfig::setAccessToken($tokenProvedor);
        try {
            $client = new PreferenceClient();
            $payload = [
                'items' => [
                    [
                        'title' => $titulo,
                        'quantity' => 1,
                        'unit_price' => $precio,
                        'currency_id' => 'UYU',
                    ],
                ],
                'back_urls' => $back_urls,
                'auto_return' => 'approved',
                'marketplace_fee' => $precio * 0.08,
                'payment_config' => [
                    'manual_capture' => true,
                ],
            ];
            $preference = $client->create($payload);
        } catch (MercadoPago\Exceptions\MPApiException $e) {
            return ['error' => 'Error al crear preferencia de pago'];
        }

        // Retornar tanto el ID como las URLs
        return [
            'id' => $preference->id,
        ];
    }

    public function pagar($idCliente, $idServicio, $idProveedor, $precio, $titulo)
    {
        // Lógica para procesar el pago
        $pagoModelo = new PagoModelo();
        $resultado = $pagoModelo->pagar($idCliente, $idServicio, $idProveedor, $precio, $titulo);
        if ($resultado !== true) {
            return ['error' => 'Error al guardar el pago'];
        }

        return $resultado;
    }

    public function confirmarPago($idCliente, $idServicio, $fechaPago, $paymentId, $estado = 'pendiente')
    {
        // Guardar el paymentId y actualizar el estado después de que el usuario paga
        $pagoModelo = new PagoModelo();

        // Guardar el paymentId
        $resultadoPaymentId = $pagoModelo->guardarPaymentId($idCliente, $idServicio, $fechaPago, $paymentId);

        // Actualizar el estado
        $resultadoEstado = $pagoModelo->actualizarEstadoPago($idCliente, $idServicio, $fechaPago, $estado);

        if ($resultadoPaymentId !== true || $resultadoEstado !== true) {
            return ['error' => 'Error al confirmar el pago'];
        }

        return [
            'success' => true,
            'message' => 'Pago confirmado con estado: ' . $estado,
            'paymentId' => $paymentId,
        ];
    }

    public function historialPagos($idCliente)
    {
        // Lógica para obtener el historial de pagos del cliente
        $pagoModelo = new PagoModelo();
        $resultado = $pagoModelo->getHistorialPagos($idCliente);

        if ($resultado === null || $resultado === false) {
            return ['error' => 'Ocurrió un error al obtener el historial de pagos'];
        }

        return $resultado;
    }

    public function historialPagosProveedor($idProveedor)
    {
        // Lógica para obtener el historial de pagos recibidos por el proveedor
        $pagoModelo = new PagoModelo();
        $resultado = $pagoModelo->getHistorialPagosProveedor($idProveedor);

        if ($resultado === null || $resultado === false) {
            return ['error' => 'Ocurrió un error al obtener el historial de pagos'];
        }

        return $resultado;
    }

    public function obtenerPreferenciaMP($precio, $titulo, $idProveedor)
    {
        $pagoModelo = new PagoModelo();
        $tokens = $pagoModelo->obtenerTokens($idProveedor);
        // Validar que se obtuvieron los tokens
        if (!$tokens || !isset($tokens['accesToken']) || empty($tokens['accesToken'])) {
            error_log("Error: No se encontraron tokens para el proveedor ID: $idProveedor");

            return ['error' => 'El proveedor no tiene tokens de MercadoPago configurados. Debe vincular su cuenta primero.'];
        }

        // Intentar desencriptar los tokens almacenados
        $accessTokenAlmacenado = $tokens['accesToken'];
        $accessToken = self::decryptString($accessTokenAlmacenado, $this->key, $this->metodo);

        // Si la desencriptación falla, el token podría estar sin encriptar (legacy)
        // Intentar usarlo directamente pero pedir re-vinculación
        if ($accessToken === null) {
            error_log("ADVERTENCIA: Token no encriptado o corrupto para proveedor ID: $idProveedor. Se requiere re-vinculación.");

            return ['error' => 'Los tokens de MercadoPago están desactualizados. Por favor, vuelve a vincular tu cuenta desde la configuración.'];
        }

        $publicKey = $tokens['publicKey'] ?? null; // Obtener la public_key también

        $resultado = self::mp($precio, $titulo, $accessToken);

        // Si hay error, retornarlo
        if (is_array($resultado) && isset($resultado['error'])) {
            return $resultado;
        }

        // Retornar la estructura correcta con el preference_id
        $return = [
            'preference_id' => $resultado['id'], // Solo el ID
            'public_key' => $publicKey,
        ];

        error_log('Preferencia creada - ID: ' . $resultado['id'] . ', Public Key: ' . $publicKey);

        return $return;
    }

    public function loginMP($code)
    {
        $clientId = self::getEnvVar('MERCADOPAGO_CLIENT_ID');
        $clientSecret = self::getEnvVar('MERCADOPAGO_CLIENT_SECRET');

        if (empty($clientId) || empty($clientSecret)) {
            return [
                'success' => false,
                'message' => 'MERCADOPAGO_CLIENT_ID o MERCADOPAGO_CLIENT_SECRET no están configuradas en las variables de entorno.',
                'code' => 500,
            ];
        }

        $redirectUri = self::getEnvVar('MERCADOPAGO_REDIRECT_URI');
        if (empty($redirectUri)) {
            return [
                'success' => false,
                'message' => 'MERCADOPAGO_REDIRECT_URI no está configurada en las variables de entorno.',
                'code' => 500,
            ];
        }

        if (!self::hasCurlExtension()) {
            return [
                'success' => false,
                'message' => 'La extensión cURL no está disponible en el entorno.',
                'code' => 500,
            ];
        }
        
        error_log('LoginMP - Redirect URI: ' . $redirectUri);
        error_log('LoginMP - Code recibido: ' . substr($code, 0, 20) . '...');

        $url = 'https://api.mercadopago.com/oauth/token';
        $data = http_build_query([
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        // Inicializar cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Ejecutar solicitud
        $response = curl_exec($ch);
        // Manejar errores de cURL
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return ['success' => false, 'message' => $error, 'code' => 500];
        }

        curl_close($ch);

        $result = json_decode($response, true);

        // Si Mercado Pago devolvió error
        if (isset($result['error'])) {
            return [
                'success' => false,
                'message' => $result['error_description'] ?? $result['error'],
                'code' => 400,
            ];
        }

        // Si la respuesta incluye el access_token
        if (isset($result['access_token'])) {
            // NO encriptamos los tokens porque:
            // 1. La conexión debería ser HTTPS (encriptación en transporte)
            // 2. Los tokens solo viajan al frontend y vuelven inmediatamente para guardarse
            // 3. Evita problemas con claves de encriptación aleatorias

            // Obtener la public_key del usuario de MercadoPago
            $publicKey = null;
            error_log('loginMP - Respuesta de MP: ' . json_encode($result));

            // Primero intentar obtener la public_key directamente de la respuesta
            $publicKey = $result['public_key'] ?? null;

            // Si no viene en la respuesta, intentar obtenerla del endpoint /users
            if (empty($publicKey) && isset($result['user_id'])) {
                error_log('loginMP - public_key no viene en respuesta, consultando user_id: ' . $result['user_id']);
                $publicKey = self::obtenerPublicKey($result['access_token'], $result['user_id']);
                error_log('loginMP - publicKey obtenida de /users: ' . ($publicKey ?? 'NULL'));
            } elseif (empty($publicKey)) {
                error_log('loginMP - WARNING: No se encontró public_key NI user_id en la respuesta de MercadoPago');
            } else {
                error_log('loginMP - public_key obtenida directamente de respuesta: ' . $publicKey);
            }

            $return = [
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'public_key' => $publicKey, // Incluir la public_key
            ];

            error_log('loginMP - Retornando: ' . json_encode([
                'has_access_token' => isset($return['access_token']),
                'has_refresh_token' => isset($return['refresh_token']),
                'has_public_key' => isset($return['public_key']),
                'public_key_value' => $return['public_key'],
            ]));

            return $return;
        }

        // Si llega aquí, hubo un problema desconocido
        return [
            'success' => false,
            'message' => 'Unexpected response from Mercado Pago',
            'data' => $response,
            'code' => 500,
        ];
    }

    public static function refreshToken($refreshToken)
    {
        $clientId = self::getEnvVar('MERCADOPAGO_CLIENT_ID');
        $clientSecret = self::getEnvVar('MERCADOPAGO_CLIENT_SECRET');

        if (empty($clientId) || empty($clientSecret)) {
            return [
                'success' => false,
                'message' => 'MERCADOPAGO_CLIENT_ID o MERCADOPAGO_CLIENT_SECRET no están configuradas en las variables de entorno.',
                'code' => 500,
            ];
        }

        if (!self::hasCurlExtension()) {
            return [
                'success' => false,
                'message' => 'La extensión cURL no está disponible en el entorno.',
                'code' => 500,
            ];
        }

        $url = 'https://api.mercadopago.com/oauth/token';

        // Armar los datos en formato x-www-form-urlencoded
        $data = http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        // Inicializar cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Ejecutar solicitud
        $response = curl_exec($ch);
        // Manejar errores de cURL
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return ['success' => false, 'message' => $error, 'code' => 500];
        }

        curl_close($ch);

        // Intentar decodificar JSON
        $result = json_decode($response, true);
        // Si Mercado Pago devolvió error
        if (isset($result['error'])) {
            return [
                'success' => false,
                'message' => $result['error_description'] ?? $result['error'],
                'code' => 400,
            ];
        }

        // Si la respuesta incluye el access_token
        if (isset($result['access_token'])) {
            return $result;
        }

        // Si llega aquí, hubo un problema desconocido
        return [
            'success' => false,
            'message' => 'Unexpected response from Mercado Pago',
            'data' => $response,
            'code' => 500,
        ];
    }

    private static function encryptString(string $plaintext, string $key, string $metodo = 'aes-256-gcm'): string
    {
        $ivlen = openssl_cipher_iv_length($metodo);
        $iv = random_bytes($ivlen);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, $metodo, $key, OPENSSL_RAW_DATA, $iv, $tag);

        // Retornamos IV + TAG + ciphertext codificado en base64
        return base64_encode($iv . $tag . $ciphertext);
    }

    private static function decryptString(string $payload_b64, string $key, string $metodo = 'aes-256-gcm'): ?string
    {
        $data = base64_decode($payload_b64, true);
        if ($data === false) {
            error_log('decryptString: base64_decode falló');

            return null;
        }

        $ivlen = openssl_cipher_iv_length($metodo);
        $taglen = 16; // AES-GCM tag length por defecto
        if (strlen($data) < ($ivlen + $taglen)) {
            error_log('decryptString: data demasiado corto. Esperado: ' . ($ivlen + $taglen) . ', Recibido: ' . strlen($data));

            return null;
        }

        $iv = substr($data, 0, $ivlen);
        $tag = substr($data, $ivlen, $taglen);
        $ciphertext = substr($data, $ivlen + $taglen);

        $plaintext = openssl_decrypt($ciphertext, $metodo, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($plaintext === false) {
            error_log('decryptString: openssl_decrypt falló. Error: ' . openssl_error_string());
        }

        return $plaintext === false ? null : $plaintext;
    }

    /**
     * Obtiene la public_key del usuario de MercadoPago.
     */
    private static function obtenerPublicKey($accessToken, $userId)
    {
        error_log("obtenerPublicKey - Iniciando para userId: $userId");
        if (!self::hasCurlExtension()) {
            error_log('obtenerPublicKey - cURL no disponible en el entorno');

            return null;
        }

        $url = "https://api.mercadopago.com/users/$userId";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        error_log("obtenerPublicKey - HTTP Code: $httpCode");

        if (curl_errno($ch)) {
            error_log('obtenerPublicKey - Error cURL: ' . curl_error($ch));
            curl_close($ch);

            return null;
        }

        curl_close($ch);

        $userData = json_decode($response, true);

        error_log('obtenerPublicKey - Respuesta de MP: ' . json_encode($userData));

        if (isset($userData['error'])) {
            error_log('obtenerPublicKey - Error de MercadoPago: ' . json_encode($userData));

            return null;
        }

        // La public_key puede venir en diferentes formatos según el ambiente
        $publicKey = $userData['public_key'] ?? $userData['test_public_key'] ?? null;

        error_log('obtenerPublicKey - Public key encontrada: ' . ($publicKey ?? 'NULL'));
        error_log('obtenerPublicKey - Claves disponibles en userData: ' . implode(', ', array_keys($userData)));

        return $publicKey;
    }

    public static function guardarTokensMP($idProveedor, $accessToken, $refreshToken, $publicKey = null)
    {
        $instance = new self();

        return $instance->guardarTokensMPInternal($idProveedor, $accessToken, $refreshToken, $publicKey);
    }

    private function guardarTokensMPInternal($idProveedor, $accessToken, $refreshToken, $publicKey = null)
    {
        // Encriptar los tokens antes de guardarlos en la base de datos
        try {
            $accessTokenEncriptado = self::encryptString($accessToken, $this->key, $this->metodo);
            $refreshTokenEncriptado = self::encryptString($refreshToken, $this->key, $this->metodo);

            error_log("Guardando tokens encriptados para proveedor ID: $idProveedor");

            $pagoModelo = new PagoModelo();
            $pagoModelo->guardarToken($idProveedor, $accessTokenEncriptado, $refreshTokenEncriptado, $publicKey);

            return true;
        } catch (Exception $e) {
            error_log('Error al guardar tokens: ' . $e->getMessage());

            return false;
        }
    }

    public function desencriptarToken($tokenEncriptado)
    {
        if ($this->key === null) {
            error_log('ERROR en desencriptarToken: key is null');

            return null;
        }

        error_log('Desencriptando token (primeros 30 chars): ' . substr($tokenEncriptado, 0, 30) . '...');
        $resultado = self::decryptString($tokenEncriptado, $this->key, $this->metodo);

        if ($resultado === null) {
            error_log('ERROR: decryptString devolvió null');
        } else {
            error_log('Token desencriptado exitosamente (primeros 20 chars): ' . substr($resultado, 0, 20) . '...');
        }

        return $resultado;
    }

    public function encriptarToken($token)
    {
        if ($this->key === null) {
            error_log('ERROR en encriptarToken: key is null');

            return null;
        }

        if (empty($token)) {
            error_log('ERROR en encriptarToken: token está vacío');

            return null;
        }

        try {
            error_log('Encriptando token (primeros 20 chars): ' . substr($token, 0, 20) . '...');
            $resultado = self::encryptString($token, $this->key, $this->metodo);

            if ($resultado === null || $resultado === false) {
                error_log('ERROR: encryptString devolvió null o false');

                return null;
            }

            error_log('Token encriptado exitosamente (longitud: ' . strlen($resultado) . ' chars)');

            return $resultado;
        } catch (Exception $e) {
            error_log('ERROR en encriptarToken: ' . $e->getMessage());

            return null;
        }
    }

    public function cancelarCompra($idCliente, $idServicio, $fechaPago)
    {
        // Lógica para cancelar una compra realmente en MercadoPago
        $pagoModelo = new PagoModelo();

        // 1. Obtener el pago con su paymentId
        $pago = $pagoModelo->obtenerPagoConPaymentId($idCliente, $idServicio, $fechaPago);

        if (!$pago) {
            return ['error' => 'Pago no encontrado'];
        }

        if ($pago['estado'] === 'fallido') {
            return ['error' => 'El pago ya está cancelado'];
        }

        // 2. Verificar si tiene paymentId (significa que se pagó con MercadoPago)
        if (empty($pago['paymentId'])) {
            // Si no tiene paymentId, solo actualizar en BD
            $pagoModelo->actualizarEstadoPago($idCliente, $idServicio, $fechaPago, 'fallido');

            return ['success' => true, 'message' => 'Pago cancelado localmente (no procesado por MercadoPago)'];
        }

        // 3. Cancelar/Reembolsar el pago en MercadoPago
        // El nombre de la columna en BD es 'accesToken' (con una 's')
        $accessToken = $pago['accesToken'];
        if (empty($accessToken)) {
            return ['error' => 'No se encontró el token de acceso del proveedor'];
        }

        $refundResult = $this->refundPaymentMP($pago['paymentId'], $accessToken);

        if (isset($refundResult['error'])) {
            error_log('Error al reembolsar en MercadoPago: ' . json_encode($refundResult));

            return ['error' => 'Error al procesar el reembolso en MercadoPago: ' . $refundResult['error']];
        }

        // 4. Actualizar el estado en la BD
        $pagoModelo->actualizarEstadoPago($idCliente, $idServicio, $fechaPago, 'fallido');

        return [
            'success' => true,
            'message' => 'Pago reembolsado exitosamente',
            'refund_id' => $refundResult['id'] ?? null,
        ];
    }

    private function refundPaymentMP($paymentId, $accessToken)
    {
        // Realizar el reembolso completo en MercadoPago
        if (!self::hasCurlExtension()) {
            return ['error' => 'La extensión cURL no está disponible en el entorno.'];
        }

        $url = "https://api.mercadopago.com/v1/payments/$paymentId/refunds";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ]);
        // Para reembolso completo, no se envía body o se envía vacío
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log('Error cURL al reembolsar: ' . $error);

            return ['error' => 'Error de conexión: ' . $error];
        }

        curl_close($ch);

        $result = json_decode($response, true);

        error_log("Respuesta de MercadoPago Refund - HTTP $httpCode: " . json_encode($result));

        // MercadoPago retorna 201 para refund exitoso
        if ($httpCode === 201 || $httpCode === 200) {
            return $result; // Contiene id, payment_id, amount, etc.
        }

        // Si hay error
        if (isset($result['message'])) {
            return ['error' => $result['message'], 'details' => $result];
        }

        return ['error' => 'Error desconocido al procesar el reembolso', 'http_code' => $httpCode, 'response' => $result];
    }

    public function aprobarPago($idCliente, $idServicio, $fechaPago)
    {
        // Lógica para aprobar un pago pendiente (capturar en MercadoPago)
        $pagoModelo = new PagoModelo();

        // 1. Obtener el pago con su paymentId
        $pago = $pagoModelo->obtenerPagoConPaymentId($idCliente, $idServicio, $fechaPago);

        if (!$pago) {
            return ['error' => 'Pago no encontrado'];
        }

        if ($pago['estado'] === 'completado') {
            return ['error' => 'El pago ya está aprobado'];
        }

        if ($pago['estado'] === 'fallido') {
            return ['error' => 'No se puede aprobar un pago cancelado'];
        }

        // 2. Verificar si tiene paymentId
        if (empty($pago['paymentId'])) {
            // Si no tiene paymentId, solo actualizar en BD
            $pagoModelo->actualizarEstadoPago($idCliente, $idServicio, $fechaPago, 'completado');

            return ['success' => true, 'message' => 'Pago aprobado localmente (no procesado por MercadoPago)'];
        }

        // 3. Capturar el pago en MercadoPago
        $accessToken = $pago['accesToken'];
        if (empty($accessToken)) {
            return ['error' => 'No se encontró el token de acceso del proveedor'];
        }

        $captureResult = $this->capturePaymentMP($pago['paymentId'], $accessToken);

        if (isset($captureResult['error'])) {
            error_log('Error al capturar pago en MercadoPago: ' . json_encode($captureResult));

            return ['error' => 'Error al procesar la aprobación en MercadoPago: ' . $captureResult['error']];
        }

        // 4. Actualizar el estado en la BD
        $pagoModelo->actualizarEstadoPago($idCliente, $idServicio, $fechaPago, 'completado');

        return [
            'success' => true,
            'message' => 'Pago aprobado y capturado exitosamente',
            'payment_status' => $captureResult['status'] ?? null,
            'captured_amount' => $captureResult['transaction_amount'] ?? null,
        ];
    }

    public function verificarContratacion($idCliente, $idServicio)
    {
        // Verificar si el cliente ha contratado el servicio (tiene un pago completado)
        $pagoModelo = new PagoModelo();
        $resultado = $pagoModelo->verificarContratacion($idCliente, $idServicio);
        
        return $resultado;
    }

    private function capturePaymentMP($paymentId, $accessToken)
    {
        // Capturar un pago pendiente en MercadoPago
        if (!self::hasCurlExtension()) {
            return ['error' => 'La extensión cURL no está disponible en el entorno.'];
        }

        $url = "https://api.mercadopago.com/v1/payments/$paymentId";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ]);
        // Enviar capture: true para capturar el pago
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['capture' => true]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log('Error cURL al capturar pago: ' . $error);

            return ['error' => 'Error de conexión: ' . $error];
        }

        curl_close($ch);

        $result = json_decode($response, true);

        error_log("Respuesta de MercadoPago Capture - HTTP $httpCode: " . json_encode($result));

        // MercadoPago retorna 200 para capture exitoso
        if ($httpCode === 200) {
            return $result; // Contiene id, status, transaction_amount, etc.
        }

        // Si hay error
        if (isset($result['message'])) {
            return ['error' => $result['message'], 'details' => $result];
        }

        return ['error' => 'Error desconocido al procesar la captura', 'http_code' => $httpCode, 'response' => $result];
    }
}
