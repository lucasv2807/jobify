<?php

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);
$Ruta = explode('/', $request);
$method = $_SERVER['REQUEST_METHOD'];
require_once 'src/controller/Reserva.php';
require_once 'src/utils/Response.php';
switch ($method) {
    case 'POST':
        // Verificar si la ruta es /reserva/crear
        if (isset($Ruta[2]) && $Ruta[2] == 'crear') {
            $crearReserva = new ReservaControlador();
            $idCliente = $_POST['idCliente'] ?? null;
            $idServicio = $_POST['idServicio'] ?? null;
            $fecha = $_POST['fecha'] ?? null;
            if (!isset($idCliente, $idServicio, $fecha)) {
                Response::error('Faltan datos obligatorios', 400);
            }
            // Llamar al método crearReserva del controlador Reserva
            $response = $crearReserva->crearReserva($idCliente, $idServicio, $fecha);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Reserva creada exitosamente', 201);
            // Verificar si la ruta es /reserva/confirmar
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'confirmar') {
            $confirmarReserva = new ReservaControlador();
            $confirmado = $_POST['confirmado'] ?? null;
            $idCliente = $_POST['idCliente'] ?? null;
            $idServicio = $_POST['idServicio'] ?? null;
            $idReserva = $_POST['idReserva'] ?? null;
            if (!isset($confirmado,  $idCliente, $idServicio, $idReserva)) {
                Response::error('Faltan datos obligatorios', 400);
            }
            // Llamar al método confirmarReserva del controlador Reserva
            $response = $confirmarReserva->confirmarReserva($confirmado, $idCliente, $idServicio, $idReserva);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Reserva confirmada exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'eliminarReserva') {
                $idReserva = $_POST['id'] ?? null;
                if (!isset($idReserva)) {
                    Response::error('Faltan datos obligatorios', 400);
                }
                $eliminarReserva = new ReservaControlador();
                $response = $eliminarReserva->eliminarReserva($idReserva);
                if (isset($response['error'])) {
                    Response::error($response['error'], 500);
                }
                Response::success($response, 'Reserva eliminada exitosamente', 200);
        } else {
            // Si la ruta no es /reserva/confirmar, retornar un error 404
            Response::notFound();
        }
        break;
    case 'GET':
        if (isset($Ruta[2]) && $Ruta[2] == 'getReservaProveedor') {
            // Lógica para manejar la obtención de reservas (si es necesario)
            $idProveedor = $_GET['id'] ?? null;
            if (!isset($idProveedor)) {
                Response::error('Faltan datos obligatorios', 400);
            }
            $getReservaProveedor = new ReservaControlador();
            $response = $getReservaProveedor->getReservaProveedor($idProveedor);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Reserva obtenida exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'getReservaCliente') {
            // Si la ruta no es /reserva/getReserva, retornar un error 404
            $idCliente = $_GET['id'] ?? null;
            if (!isset($idCliente)) {
                Response::error('Faltan datos obligatorios', 400);
            }
            $getReservaCliente = new ReservaControlador();
            $response = $getReservaCliente->getReservaCliente($idCliente);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Reserva obtenida exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'getReservaById') {
            // Si la ruta no es /reserva/getReserva, retornar un error 404
            $idReserva = $_GET['id'] ?? null;
            if (!isset($idReserva)) {
                Response::error('Faltan datos obligatorios', 400);
            }
            $getReservaById = new ReservaControlador();
            $response = $getReservaById->getReservaById($idReserva);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Reserva obtenida exitosamente', 200);
        } else {
            // Si la ruta no existe, retornar un error 404
            Response::notFound();
        }
        break;
        // Lógica para manejar solicitudes DELETE (si es necesario)
}
