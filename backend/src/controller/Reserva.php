<?php

require_once 'src/model/Reserva.php';
require_once 'src/controller/Chat.php';
require_once 'src/controller/Servicio.php';
class ReservaControlador
{
    public function crearReserva($idCliente, $idServicio, $fecha)
    {
        // Lógica para crear una reserva
        // Validar los datos recibidos
        $ReservaModelo = new ReservaModelo();
        if (empty($idCliente) || empty($idServicio) || empty($fecha)) {
            return ['error' => 'Faltan datos obligatorios'];
        }

        // Validar que el servicio existe
        $ServicioController = new ServicioControlador();
        $servicio = $ServicioController->getServicio($idServicio);
        if (!$servicio || isset($servicio['error'])) {
            return ['error' => 'El servicio con ID ' . $idServicio . ' no existe'];
        }

        try {
            $resultado = $ReservaModelo->crearReserva($idCliente, $idServicio, $fecha);
            if ($resultado) {
                $chatController = new ChatControlador();
                $idProveedor = $servicio['idProveedor'];
                $hasChats = $chatController->checkChatExists($idCliente, $idServicio);
                if (!$hasChats) {
                    $ChatModelo = new ChatModelo();
                    $ChatModelo->createChat($idCliente, $idServicio, $idProveedor);
                }

                return ['result' => $resultado];
            } else {
                return ['error' => 'Error al crear la reserva'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function confirmarReserva($confirmado, $idCliente, $idServicio, $idReserva)
    {
        // Lógica para confirmar una reserva
        $ReservaModelo = new ReservaModelo();
        $resultado = $ReservaModelo->confirmarReserva($confirmado, $idCliente, $idServicio, $idReserva);
        if ($resultado) {
            return ['success' => true];
        } else {
            return ['error' => 'Error al confirmar la reserva'];
        }
    }

    public function getReservaProveedor($idProveedor)
    {
        $ReservaModelo = new ReservaModelo();
        $resultado = $ReservaModelo->getReservaProveedor($idProveedor);
        if ($resultado !== false) {
            $resultado = array_map(function ($reserva) {
                return ['tipo' => 'schedule', ...$reserva];
            }, $resultado);

            return $resultado;
        } else {
            return ['error' => 'Error al obtener la reserva'];
        }
    }

    public function getReservaCliente($idCliente)
    {
        $ReservaModelo = new ReservaModelo();
        $resultado = $ReservaModelo->getReservaCliente($idCliente);
        if ($resultado !== false) {
            $resultado = array_map(function ($reserva) {
                return ['tipo' => 'schedule', ...$reserva];
            }, $resultado);

            return $resultado;
        } else {
            return ['error' => 'Error al obtener la reserva'];
        }
    }
    public function getReservaById($idReserva)
    {
        $ReservaModelo = new ReservaModelo();
        $resultado = $ReservaModelo->getReservaById($idReserva);
        if ($resultado !== false) {
            return ['tipo' => 'schedule', ...$resultado];
        } else {
            return ['error' => 'Error al obtener la reserva'];
        }
    }
    public function eliminarReserva($idReserva)
    {
        $ReservaModelo = new ReservaModelo();
        $resultado = $ReservaModelo->eliminarReserva($idReserva);
        if ($resultado) {
            return ['success' => true];
        } else {
            return ['error' => 'Error al eliminar la reserva'];
        }
    }
}
