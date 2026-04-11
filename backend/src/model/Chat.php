<?php

require_once 'src/DB.php';

class ChatModelo
{
    protected static function getPDO()
    {
        try {
            return DB::connect();
        } catch (PDOException $e) {
            throw new Exception('Error de conexión a la base de datos');
        }
    }

    public static function getChatsCliente($idCliente)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT Contacta.* , Usuario.nombre AS nombre, Usuario.apellido AS apellido, Usuario.imagen, Servicio.titulo AS servicioTitulo
                FROM Contacta
                JOIN Usuario ON Contacta.idProveedor = Usuario.idUsuario
                JOIN Servicio ON Contacta.idServicio = Servicio.idServicio
        WHERE idCliente = :idCliente';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->execute();

        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $chats;
    }

    public static function getChatsProveedor($idProveedor)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT Contacta.*, Usuario.nombre AS nombre, Usuario.apellido AS apellido, Usuario.imagen, Servicio.titulo AS servicioTitulo
                FROM Contacta
                JOIN Usuario ON Contacta.idCliente = Usuario.idUsuario
                JOIN Servicio ON Contacta.idServicio = Servicio.idServicio
        WHERE Contacta.idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();

        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $chats;
    }

    public static function chatExists($idCliente, $idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT COUNT(*) FROM Contacta WHERE idCliente = :idCliente AND idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Devuelve la fila de la tabla Contacta para un chat específico
     * o null si no existe.
     */
    public static function getChat($idCliente, $idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT Contacta.*, 
                Contrata.confirmado, 
                Contrata.idReserva,
                Cliente.imagen AS imagenCliente,
                Cliente.nombre AS nombreCliente,
                Cliente.apellido AS apellidoCliente,
                Proveedor.imagen AS imagenProveedor,
                Proveedor.nombre AS nombreProveedor,
                Proveedor.apellido AS apellidoProveedor
        FROM Contacta 
        JOIN Contrata ON Contacta.idCliente = Contrata.idCliente AND Contacta.idServicio = Contrata.idServicio
        JOIN Usuario AS Cliente ON Contacta.idCliente = Cliente.idUsuario
        JOIN Usuario AS Proveedor ON Contacta.idProveedor = Proveedor.idUsuario
        WHERE Contacta.idCliente = :idCliente AND Contacta.idServicio = :idServicio LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function createChat($idCliente, $idServicio, $idProveedor)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Contacta (idCliente, idServicio, idProveedor) VALUES (:idCliente, :idServicio, :idProveedor)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();

        return true;
    }

    public static function insertMessage(int $idCliente, int $idServicio, string $mensaje, int $idEmisor)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Contacta_Mensaje (idCliente, idServicio, idEmisor, mensaje) VALUES (:idCliente, :idServicio, :idEmisor, :mensaje)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idEmisor', $idEmisor);
        $stmt->bindParam(':mensaje', $mensaje);
        $stmt->execute();

        return true;
    }

    public static function getMessageHistory($idCliente, $idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT Contacta_Mensaje.*
        FROM Contacta_Mensaje 
        WHERE Contacta_Mensaje.idServicio = :idServicio AND Contacta_Mensaje.idCliente = :idCliente';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
