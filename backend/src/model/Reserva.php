<?php

require_once 'src/DB.php';

class ReservaModelo
{
    protected static function getPDO()
    {
        try {
            return DB::connect();
        } catch (PDOException $e) {
            throw new Exception('Error de conexión a la base de datos');
        }
    }

    public function crearReserva($idCliente, $idServicio, $fecha)
    {
        // Lógica para insertar la reserva en la base de datos
        $pdo = self::getPDO();
        $confirmado = 0;
        $sql = 'INSERT INTO Contrata (idCliente, idServicio, fecha, confirmado) VALUES (:idCliente, :idServicio, :fecha, :confirmado)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':confirmado', $confirmado);
        $stmt->execute();
        $idReserva = $pdo->lastInsertId();

        return $idReserva;
    }

    public function confirmarReserva($confirmado, $idCliente, $idServicio, $idReserva)
    {
        // Lógica para actualizar la reserva en la base de datos
        $pdo = self::getPDO();
        $sql = 'UPDATE Contrata SET confirmado = :confirmado WHERE idCliente = :idCliente AND idServicio = :idServicio AND idReserva = :idReserva';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':confirmado', $confirmado);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idReserva', $idReserva);

        return $stmt->execute();
    }

    public function getReservaProveedor($idProveedor)
    {
        // Lógica para obtener una reserva por su ID y proveedor
        $pdo = self::getPDO();
        $sql = 'SELECT Contrata.*, 
                Servicio.titulo AS servicioTitulo, 
                Usuario.nombre AS nombreCliente, 
                Usuario.apellido AS apellidoCliente, 
                Usuario.imagen AS imagen
        FROM Contrata 
        INNER JOIN Servicio ON Contrata.idServicio = Servicio.idServicio 
        INNER JOIN Usuario ON Contrata.idCliente = Usuario.idUsuario
        WHERE Servicio.idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservaCliente($idCliente)
    {
        // Lógica para obtener una reserva por su ID y cliente
        $pdo = self::getPDO();
        $sql = 'SELECT Contrata.*, Servicio.titulo AS servicioTitulo, Usuario.nombre AS nombreProveedor, Usuario.apellido AS apellidoProveedor, Usuario.imagen AS imagen
        FROM Contrata 
        JOIN Servicio ON Contrata.idServicio = Servicio.idServicio
        JOIN Usuario ON Servicio.idProveedor = Usuario.idUsuario
        WHERE idCliente = :idCliente';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservaById($idReserva)
    {
        // Lógica para obtener una reserva por su ID
        $pdo = self::getPDO();
        $sql = 'SELECT 
                Contrata.idReserva,
                Contrata.idCliente, 
                Contrata.idServicio, 
                Contrata.fecha, 
                Contrata.confirmado,
                Servicio.titulo,
                Servicio.precio,
                Servicio.descripcion,
                Usuario.idUsuario,
                Usuario.nombre, 
                Usuario.apellido, 
                Usuario.imagen,
                Usuario.email,
                Usuario.fechaNac
        FROM Contrata 
        JOIN Servicio ON Contrata.idServicio = Servicio.idServicio
        JOIN Usuario ON Contrata.idCliente = Usuario.idUsuario
        WHERE idReserva = :idReserva';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idReserva', $idReserva);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function eliminarReserva($idReserva)
    {
        // Lógica para eliminar una reserva por su ID
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Contrata WHERE idReserva = :idReserva';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idReserva', $idReserva);

        return $stmt->execute();
    }
}
