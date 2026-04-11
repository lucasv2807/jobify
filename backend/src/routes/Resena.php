<?php

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);
$Ruta = explode('/', $request);
$method = $_SERVER['REQUEST_METHOD'];
require_once 'src/controller/Resena.php';
require_once 'src/utils/Response.php';
switch ($method) {
    case 'POST':
        if (isset($Ruta[2]) && $Ruta[2] == 'subirReview') {
            $servicio = new resenaControlador();
            $idCliente = $_POST['idCliente'] ?? null;
            $idServicio = $_POST['idServicio'] ?? null;
            $calificacion = $_POST['calificacion'] ?? null;
            $comentario = $_POST['comentario'] ?? '';
            // Solo pasar la imagen si fue enviada correctamente
            $imagen = (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) ? $_FILES['imagen'] : null;
            $response = $servicio->crearresena($idCliente, $idServicio, $calificacion, $comentario, $imagen);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'resena enviada exitosamente', 201);
        } else {
            Response::notFound();
        }
        break;
    case 'GET':
        if (isset($Ruta[2]) && $Ruta[2] == 'getReview') {
            $getReview = new resenaControlador();
            $idServicio = $_GET['idServicio'] ?? null;
            $response = $getReview->getresena($idServicio);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'resena obtenida exitosamente', 200);
        } else {
            Response::notFound();
        }
        break;
    default:
        Response::error('Método no permitido', 405);
        break;
}
