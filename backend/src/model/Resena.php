<?php

require_once 'src/DB.php';
class resenaModelo
{
    protected static function getPDO()
    {
        try {
            return DB::connect();
        } catch (PDOException $e) {
            // Puedes lanzar una excepción personalizada o devolver null/false
            throw new Exception('Error de conexión a la base de datos');
        }
    }

    public function crearresena($calificacion, $comentario, $imagen)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Resena (calificacion, comentario, imagen) VALUES (:calificacion, :comentario, :imagen)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':calificacion', $calificacion);
        $stmt->bindParam(':comentario', $comentario);
        $stmt->bindParam(':imagen', $imagen);
        $stmt->execute();

        return $pdo->lastInsertId();
    }

    public function crearPublica($idCliente, $idServicio, $idResena)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Publica (idCliente, idServicio, idResena) VALUES (:idCliente, :idServicio, :idResena)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idResena', $idResena);

        return $stmt->execute();
    }

    public function getresenasByServicio($idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT r.idResena, r.calificacion, r.comentario, r.imagen, u.nombre AS nombreCliente, u.apellido AS apellidoCliente, u.imagen AS imagenCliente, p.idCliente as idCliente
                FROM Resena r
                JOIN Publica p ON r.idResena = p.idResena
                JOIN Usuario u ON p.idCliente = u.idUsuario
                WHERE p.idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
