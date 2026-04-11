<?php

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);
$Ruta = explode('/', $request);
$method = $_SERVER['REQUEST_METHOD'];

require_once 'src/controller/Admin.php';
require_once 'src/utils/Response.php';

switch ($method) {
    case 'POST':
        if (isset($Ruta[2]) && $Ruta[2] == 'verifyPassword') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (strpos($contentType, 'application/json') !== false) {
                $input = json_decode(file_get_contents('php://input'), true);
            } else {
                $input = $_POST;
            }

            $password = $input['password'] ?? null;

            if (!$password) {
                Response::error('Contraseña requerida', 400);
                break;
            }

            $response = AdminControlador::verifyAdminPassword($password);

            if ($response['success']) {
                Response::success($response, 'Acceso concedido', 200);
            } else {
                Response::error($response['message'], 401);
            }
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'register') {
            // Manejar tanto FormData como JSON
            // la verdad no se por que se hizo esto pero todo funciona ahora
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (strpos($contentType, 'application/json') !== false) {
                $input = json_decode(file_get_contents('php://input'), true);
            } else {
                $input = $_POST;
            }
            $tipo = $input['tipo'] ?? null;
            $email = $input['email'] ?? null;
            $nombre = $input['nombre'] ?? null;
            $apellido = $input['apellido'] ?? null;
            $password = $input['password'] ?? null;
            $fecha_nacimiento = $input['fecha_nacimiento'] ?? null;

            // Validar que todos los campos requeridos estén presentes
            if (!$tipo || !$email || !$nombre || !$apellido || !$password || !$fecha_nacimiento) {
                error_log('Datos recibidos: ' . json_encode($input));
                error_log('Content-Type: ' . $contentType);
                Response::error('Faltan campos requeridos. Recibido: ' . json_encode($input), 400);
                break;
            }

            $response = AdminControlador::register($nombre, $apellido, $email, $password, $fecha_nacimiento, $tipo);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Usuario registrado exitosamente', 201);
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'deleteUser') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                Response::error('Falta el ID del usuario a eliminar', 400);
            }
            $response = AdminControlador::deleteUser($id);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Usuario eliminado exitosamente', 200);
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'deleteService') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                Response::error('Falta el ID del servicio a eliminar', 400);
            }
            $response = AdminControlador::deleteService($id);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Servicio eliminado exitosamente', 200);
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'deleteReview') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                Response::error('Falta el ID de la reseña a eliminar', 400);
            }
            $response = AdminControlador::deleteReview($id);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Reseña eliminada exitosamente', 200);
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'reviewModify') {
            $id = $_POST['id'] ?? null;
            $comentario = $_POST['comentario'] ?? null;
            $calificacion = $_POST['calificacion'] ?? null;
            $imagen = $_FILES['imagen'] ?? null; // opcional
            if ($calificacion === null) {
                Response::error('Falta la calificación de la reseña a modificar', 400);
            }
            if ($comentario === null) {
                Response::error('Falta el comentario de la reseña a modificar', 400);
            }
            if (!$id) {
                Response::error('Falta el ID de la reseña a modificar', 400);
            }
            $response = AdminControlador::reviewModify($id, $comentario, $calificacion, $imagen);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Estadísticas del dashboard obtenidas exitosamente', 200);
        }
        break;
    case 'GET':
        if (isset($Ruta[2]) && $Ruta[2] == 'userList') {
            $users = AdminControlador::getUserList();
            if (isset($users['error'])) {
                Response::error($users['error'], 500);
            }
            Response::success($users, 'Lista de usuarios obtenida exitosamente', 200);
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'serviceList') {
            $service = AdminControlador::getServiceList();
            if (isset($service['error'])) {
                Response::error($service['error'], 500);
            }
            Response::success($service, 'Lista de servicios obtenida exitosamente', 200);
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'reviewList') {
            $stats = AdminControlador::getReviewList();
            if (isset($stats['error'])) {
                Response::error($stats['error'], 500);
            }
            Response::success($stats, 'Estadísticas del dashboard obtenidas exitosamente', 200);
        }
}
