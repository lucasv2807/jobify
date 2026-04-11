<?php

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);
$Ruta = explode('/', $request);
$method = $_SERVER['REQUEST_METHOD'];
require_once 'src/controller/Chat.php';
require_once 'src/utils/Response.php';

switch ($method) {
    case 'POST':
        if (isset($Ruta[2]) && $Ruta[2] == 'send') {
            $send = new ChatControlador();
            $idCliente = $_POST['idCliente'] ?? null;
            $idServicio = $_POST['idServicio'] ?? null;
            $mensaje = $_POST['mensaje'] ?? null;
            $idEmisor = $_POST['idEmisor'] ?? null;
            if (!isset($idCliente, $idServicio, $mensaje, $idEmisor)) {
                Response::error('Faltan datos. idCliente: ' . $idCliente . ', idServicio: ' . $idServicio . ', mensaje: ' . $mensaje . ', idEmisor: ' . $idEmisor, 400);
            }
            $response = $send->sendMessage($idCliente, $idServicio, $mensaje, $idEmisor);
            if (isset($response['error'])) {
                Response::error('Error al enviar el mensaje', 500, $response['error']);
            }
            Response::success(['success' => $response], 'Mensaje enviado exitosamente', 201);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'crear') {
            $create = new ChatControlador();
            $idCliente = $_POST['idCliente'] ?? null;
            $idServicio = $_POST['idServicio'] ?? null;
            $idProveedor = $_POST['idProveedor'] ?? null;
            if (!isset($idCliente, $idServicio, $idProveedor)) {
                Response::error('Faltan datos', 400);
            }
            $response = $create->createChat($idCliente, $idServicio, $idProveedor);
            if (isset($response['error'])) {
                Response::error('Error al crear el chat', 500, $response['error']);
            }
            Response::success(['success' => $response], 'Chat creado exitosamente', 201);
        } else {
            Response::notFound();
        }
        break;
    case 'GET':
        if (isset($Ruta[2]) && $Ruta[2] == 'history') {
            $mensajeria = new ChatControlador();
            $idCliente = $_GET['idCliente'] ?? null;
            $idServicio = $_GET['idServicio'] ?? null;
            $idProveedor = $_GET['idProveedor'] ?? null;
            if (!isset($idCliente, $idServicio, $idProveedor)) {
                Response::error('Faltan datos', 400);
            }
            $response = $mensajeria->getMessageHistory($idCliente, $idServicio, $idProveedor);
            if (isset($response['error'])) {
                Response::error('Error al obtener el historial de mensajes', 500, $response['error']);
            }
            Response::success($response, 'Historial de mensajes obtenido exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'chatsCliente') {
            $chatController = new ChatControlador();
            $idCliente = $_GET['id'] ?? null;
            if (!isset($idCliente)) {
                Response::error('Faltan datos', 400);
            }
            $response = $chatController->getChatsCliente($idCliente);
            if (isset($response['error'])) {
                Response::error('Error al obtener los chats del cliente', 500, $response['error']);
            } else {
                Response::success($response, 'Chats del cliente obtenidos exitosamente', 200);
            }
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'chatsProveedor') {
            $chatController = new ChatControlador();
            $idProveedor = $_GET['id'] ?? null;
            if (!isset($idProveedor)) {
                Response::error('Faltan datos', 400);
            }
            $response = $chatController->getChatsProveedor($idProveedor);
            if (isset($response['error'])) {
                Response::error('Error al obtener los chats del proveedor', 500, $response['error']);
            } else {
                Response::success($response, 'Chats del proveedor obtenidos exitosamente', 200);
            }
        } else {
            Response::notFound();
        }
        break;
}
