<?php

require_once 'src/model/Chat.php';
require_once 'src/model/Usuario.php';

class ChatControlador
{
    public static function sendMessage($idCliente, $idServicio, $mensaje, $idEmisor)
    {
        if (empty($mensaje)) {
            return ['error' => 'El mensaje no puede estar vacío'];
        }

        try {
            $chat = new ChatModelo();
            $usuario = new UsuarioModelo();
            $idC = $usuario->getUsuarioById($idCliente);
            if ($idC === null) {
                return ['error' => 'Este cliente no existe'];
            }
            $res = $chat->insertMessage($idCliente, $idServicio, $mensaje, $idEmisor);
            if ($res === null) {
                return ['error' => 'Ocurrió un error al enviar el mensaje'];
            }

            return $res;
        } catch (Exception $e) {
            return ['error' => 'Este cliente no existe'];
        }
    }

    public static function createChat($idCliente, $idServicio, $idProveedor)
    {
        try {
            $chat = new ChatModelo();
            $res = $chat->createChat($idCliente, $idServicio, $idProveedor);
            if ($res === null) {
                return ['error' => 'Ocurrió un error al crear el chat'];
            }

            return $res;
        } catch (Exception $e) {
            return ['error' => 'Ocurrió un error al crear el chat'];
        }
    }

    public static function getMessageHistory($idCliente, $idServicio, $idProveedor)
    {
        try {
            $chat = new ChatModelo();
            // Obtener la fila del chat para validar propietario
            $chatRow = ChatModelo::getChat($idCliente, $idServicio);
            if ($chatRow === null) {
                return ['error' => 'El chat no existe'];
            }
            if ((int) $chatRow['idProveedor'] !== (int) $idProveedor) {
                return ['error' => 'El proveedor no tiene acceso a este chat'];
            }

            $messages = $chat->getMessageHistory($idCliente, $idServicio);

            return ['chat' => $chatRow, 'messages' => $messages];
        } catch (Exception $e) {
            return ['error' => 'El chat no existe'];
        }
    }

    public static function getChatsCliente($idCliente)
    {
        try {
            $chat = new ChatModelo();
            $res = $chat->getChatsCliente($idCliente);
            if ($res === false) {
                return ['error' => 'Ocurrió un error al obtener los chats del cliente'];
            }
            // agregar el tipo de dato chat a cada elemento
            $res = array_map(function ($chat) {
                return ['tipo' => 'Chat', ...$chat];
            }, $res);

            return $res;
        } catch (Exception $e) {
            return ['error' => 'Ocurrió un error al obtener los chats del cliente'];
        }
    }

    public static function getChatsProveedor($idProveedor)
    {
        try {
            $chat = new ChatModelo();
            $res = $chat->getChatsProveedor($idProveedor);
            if ($res === false) {
                return ['error' => 'Ocurrió un error al obtener los chats del proveedor'];
            }
            // agregar el tipo de dato chat a cada elemento
            $res = array_map(function ($chat) {
                return ['tipo' => 'Chat', ...$chat];
            }, $res);

            return $res;
        } catch (Exception $e) {
            return ['error' => 'Ocurrió un error al obtener los chats del proveedor'];
        }
    }

    public static function checkChatExists($idCliente, $idServicio)
    {
        try {
            $chat = new ChatModelo();

            return $chat->chatExists($idCliente, $idServicio);
        } catch (Exception $e) {
            return false;
        }
    }
}
