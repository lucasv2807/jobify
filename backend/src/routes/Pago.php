<?php

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);
$Ruta = explode('/', $request);
$method = $_SERVER['REQUEST_METHOD'];
require_once 'src/controller/Pago.php';
require_once 'src/utils/Response.php';

switch ($method) {
    case 'POST':
        if (isset($Ruta[2]) && $Ruta[2] == 'pagar') {
            $pagoControlador = new PagoControlador();
            $idCliente = isset($_POST['idCliente']) ? trim($_POST['idCliente']) : null;
            $idServicio = isset($_POST['idServicio']) ? trim($_POST['idServicio']) : null;
            $idProveedor = isset($_POST['idProveedor']) ? trim($_POST['idProveedor']) : null;
            $precio = isset($_POST['precio']) ? trim($_POST['precio']) : null;
            $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : null;
            // Validaciones básicas: no vacíos y precio numérico
            if (empty($idCliente) || empty($idServicio) || empty($idProveedor) || empty($precio) || empty($titulo)) {
                Response::error('Faltan datos obligatorios o están vacíos', 400);
            }
            if (!is_numeric($precio) || floatval($precio) <= 0) {
                Response::error('Precio inválido', 400);
            }
            $response = $pagoControlador->pagar($idCliente, $idServicio, $idProveedor, $precio, $titulo);
            if (is_array($response) && isset($response['error'])) {
                // Propagar detalle de error si existe
                Response::error('Error al procesar el pago', 500, json_encode($response));
            }
            Response::success(['funciono' => $response], 'Pago guardado exitosamente', 201);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'saveTokens') {
            try {
                $pagoControlador = new PagoControlador();
                $refreshToken = $_POST['refresh_token'] ?? null;
                $accessToken = $_POST['access_token'] ?? null;
                $publicKey = $_POST['public_key'] ?? null; // Recibir la public_key
                $idProveedor = $_POST['id_proveedor'] ?? null;

                error_log("saveTokens - idProveedor: $idProveedor");

                if (!isset($idProveedor)) {
                    Response::error('Falta el ID del proveedor', 400);
                }

                if (empty($accessToken) || empty($refreshToken)) {
                    Response::error('Faltan los tokens', 400);
                }

                // Los tokens vienen en TEXTO PLANO desde el callback de MercadoPago
                // No necesitan desencriptarse, se encriptarán al guardarse en la BD
                error_log('Recibiendo tokens en texto plano del callback de MercadoPago...');
                error_log('Access token (primeros 30 chars): ' . substr($accessToken, 0, 30) . '...');
                error_log('Refresh token (primeros 30 chars): ' . substr($refreshToken, 0, 30) . '...');

                // Guardamos los tokens directamente (se encriptarán en guardarTokensMP)
                error_log('Guardando tokens y public_key en BD...');
                $resultado = $pagoControlador->guardarTokensMP($idProveedor, $accessToken, $refreshToken, $publicKey);

                if ($resultado === false) {
                    error_log('ERROR: guardarTokensMP devolvió false');
                    Response::error('Error al guardar los tokens en la base de datos', 500);
                }

                error_log("Tokens guardados exitosamente para proveedor ID: $idProveedor");
                Response::success([], 'Tokens guardados correctamente', 200);
            } catch (Exception $e) {
                error_log('Exception en saveTokens: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                Response::error('Error al guardar tokens: ' . $e->getMessage(), 500);
            }
        } else {
            Response::notFound();
        }
        break;
    case 'GET':
        if (isset($Ruta[2]) && $Ruta[2] == 'historialCliente') {
            $pagoControlador = new PagoControlador();
            $idCliente = $_GET['id'] ?? null;
            if (!isset($idCliente)) {
                Response::error('Faltan datos. idCliente: ' . $idCliente, 400);
            }
            $response = $pagoControlador->historialPagos($idCliente);
            if (isset($response['error'])) {
                Response::error('Error al obtener el historial de pagos', 500, $response['error']);
            }
            Response::success($response, 'Historial de pagos obtenido exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'historialProveedor') {
            $pagoControlador = new PagoControlador();
            $idProveedor = $_GET['id'] ?? null;
            if (!isset($idProveedor)) {
                Response::error('Faltan datos. idProveedor: ' . $idProveedor, 400);
            }
            $response = $pagoControlador->historialPagosProveedor($idProveedor);
            if (isset($response['error'])) {
                Response::error('Error al obtener el historial de pagos del proveedor', 500, $response['error']);
            }
            Response::success($response, 'Historial de pagos del proveedor obtenido exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'mp') {
            $pagoControlador = new PagoControlador();
            // Normalizar valores que pueden venir como la cadena 'undefined' desde frontends
            $precio = $_GET['precio'] ?? null;
            $titulo = $_GET['titulo'] ?? null;
            $idProveedor = $_GET['idProveedor'] ?? null;
            if ($precio === null || $titulo === null || $precio === 'undefined' || $titulo === 'undefined') {
                Response::error('Faltan datos obligatorios: precio y titulo', 400);
            }
            $precio = floatval($precio);
            if (!is_numeric($precio) || $precio <= 0) {
                Response::error('Precio inválido', 400);
            }
            if ($idProveedor === null || $idProveedor === 'undefined') {
                Response::error('Falta idProveedor', 400);
            }
            $response = $pagoControlador->obtenerPreferenciaMP($precio, $titulo, $idProveedor);
            if (is_array($response) && isset($response['error'])) {
                Response::error($response['error'] ?? 'Error al obtener preferencia', 500, $response['details'] ?? null);
            }
            // Devolver la preferencia bajo la clave 'preferenciaMP' para que el front la desestructure correctamente
            Response::success(['preferenciaMP' => $response], 'Preferencia de pago obtenida exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'verificarContratacion') {
            $pagoControlador = new PagoControlador();
            $idCliente = $_GET['idCliente'] ?? null;
            $idServicio = $_GET['idServicio'] ?? null;
            
            if (!isset($idCliente) || !isset($idServicio)) {
                Response::error('Faltan datos obligatorios: idCliente, idServicio', 400);
            }
            
            $response = $pagoControlador->verificarContratacion($idCliente, $idServicio);
            
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            
            Response::success(['contratado' => $response], 'Verificación completada', 200);
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'callback') {
            $pagoControlador = new PagoControlador();

            if (!isset($_GET['code']) || empty($_GET['code'])) {
                Response::error('No se recibió el authorization code', 400);
            }

            $code = $_GET['code'];

            error_log('Callback - Código recibido, llamando loginMP...');
            $response = $pagoControlador->loginMP($code);

            error_log('Callback - Respuesta de loginMP: ' . json_encode([
                'has_access_token' => isset($response['access_token']),
                'has_refresh_token' => isset($response['refresh_token']),
                'has_public_key' => isset($response['public_key']),
                'public_key_value' => $response['public_key'] ?? 'NO EXISTE',
            ]));

            // Verificar si hay error
            if (is_array($response) && isset($response['success']) && $response['success'] === false) {
                error_log('Error en loginMP: ' . json_encode($response));
                Response::error(
                    $response['message'] ?? 'Error al iniciar sesión en MP',
                    $response['code'] ?? 500
                );
            }

            // Verificar que tenemos los tokens
            if (!isset($response['access_token']) || !isset($response['refresh_token'])) {
                error_log('Respuesta inesperada de loginMP: ' . json_encode($response));
                Response::error('Error: no se recibieron los tokens', 500);
            }

            $access_token = $response['access_token'];
            $refresh_token = $response['refresh_token'];
            $public_key = $response['public_key'] ?? ''; // Obtener la public_key si existe

            if (empty($public_key)) {
                error_log('WARNING Callback - public_key está vacía!');
            }

            $redirectUrl = 'https://jobifyuy.app/confirm?requestId=' . urlencode($access_token) . '&requestType=' . urlencode($refresh_token) . '&publicKey=' . urlencode($public_key);

            error_log('Callback - URL de redirección con tokens encriptados generada');
            error_log('Callback - public_key que se enviará: ' . ($public_key ?: 'VACÍA'));

            header('Location: ' . $redirectUrl);
            exit();
        }
        break;
    case 'PUT':
        if (isset($Ruta[2]) && $Ruta[2] == 'confirmar-pago') {
            // Endpoint para guardar el paymentId después de que el usuario paga
            $pagoControlador = new PagoControlador();
            $data = json_decode(file_get_contents('php://input'), true);

            $idCliente = $data['idCliente'] ?? null;
            $idServicio = $data['idServicio'] ?? null;
            $fechaPago = $data['fechaPago'] ?? null;
            $paymentId = $data['paymentId'] ?? null;
            $estado = $data['estado'] ?? 'pendiente'; // pendiente, completado, fallido

            if (!isset($idCliente) || !isset($idServicio) || !isset($fechaPago) || !isset($paymentId)) {
                Response::error('Faltan datos obligatorios: idCliente, idServicio, fechaPago, paymentId', 400);
            }

            $response = $pagoControlador->confirmarPago($idCliente, $idServicio, $fechaPago, $paymentId, $estado);

            if (isset($response['error'])) {
                Response::error($response['error'], 400);
            }

            Response::success($response, 'Pago confirmado exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'cancelar') {
            $pagoControlador = new PagoControlador();

            // Obtener datos del body
            $data = json_decode(file_get_contents('php://input'), true);

            $idCliente = $data['idCliente'] ?? null;
            $idServicio = $data['idServicio'] ?? null;
            $fechaPago = $data['fechaPago'] ?? null;

            if (!isset($idCliente) || !isset($idServicio) || !isset($fechaPago)) {
                Response::error('Faltan datos obligatorios: idCliente, idServicio, fechaPago', 400);
            }

            $response = $pagoControlador->cancelarCompra($idCliente, $idServicio, $fechaPago);

            if (isset($response['error'])) {
                Response::error($response['error'], 400);
            }

            Response::success($response, 'Compra cancelada exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'aprobar') {
            $pagoControlador = new PagoControlador();

            // Obtener datos del body
            $data = json_decode(file_get_contents('php://input'), true);

            $idCliente = $data['idCliente'] ?? null;
            $idServicio = $data['idServicio'] ?? null;
            $fechaPago = $data['fechaPago'] ?? null;

            if (!isset($idCliente) || !isset($idServicio) || !isset($fechaPago)) {
                Response::error('Faltan datos obligatorios: idCliente, idServicio, fechaPago', 400);
            }

            $response = $pagoControlador->aprobarPago($idCliente, $idServicio, $fechaPago);

            if (isset($response['error'])) {
                Response::error($response['error'], 400);
            }

            Response::success($response, 'Pago aprobado exitosamente', 200);
        } else {
            Response::notFound();
        }
        break;
    default:
        Response::notFound();
        break;
}
