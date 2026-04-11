<?php

require_once 'src/DB.php';

class AdminModelo
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

    public static function crearModerador($id, $hoy)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Moderador (idModerador, fechaContratacion) VALUES (:idModerador, :fechaContratacion)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idModerador', $id);
        $stmt->bindParam(':fechaContratacion', $hoy);
        $stmt->execute();
    }
    public static function fetchAllUsers()
    {
        $pdo = self::getPDO();
        $sql = 'SELECT u.idUsuario as id, u.nombre, u.apellido, u.email, u.fechaNac, u.imagen, ut.telefono,
                       CASE
                           WHEN c.idCliente IS NOT NULL THEN "Cliente"
                           WHEN p.idProveedor IS NOT NULL THEN "Proveedor"
                           WHEN m.idModerador IS NOT NULL THEN "Moderador"
                           ELSE "Desconocido"
                       END AS tipoUsuario
                FROM Usuario u
                JOIN Usuario_Telefono ut ON u.idUsuario = ut.idUsuario
                LEFT JOIN Cliente c ON u.idUsuario = c.idCliente
                LEFT JOIN Proveedor p ON u.idUsuario = p.idProveedor
                LEFT JOIN Moderador m ON u.idUsuario = m.idModerador';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function deleteUser($id)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Usuario WHERE idUsuario = :idUsuario';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idUsuario', $id);
        $stmt->execute();
    }
    public static function fetchAllServices()
    {
        $pdo = self::getPDO();
        $sql = 'SELECT s.idServicio as id, s.titulo, s.descripcion, s.precio, s.idProveedor, 
                       GROUP_CONCAT(sc.categoria SEPARATOR ", ") as categorias
                FROM Servicio s
                LEFT JOIN Servicio_Categoria sc ON s.idServicio = sc.idServicio
                GROUP BY s.idServicio, s.titulo, s.descripcion, s.precio, s.idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function deleteService($id)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Servicio WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $id);
        $stmt->execute();
    }
    public static function fetchAllReviews()
    {
        $pdo = self::getPDO();
        $sql = 'SELECT r.idResena as id, r.calificacion, r.comentario, pr.idCliente, pr.idServicio
                FROM Resena r
                JOIN Publica pr ON r.idResena = pr.idResena';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function deleteReview($id)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Resena WHERE idResena = :idResena';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idResena', $id);
        $stmt->execute();
    }
    public static function modifyReview($id, $comentario, $calificacion, $imagenUrl)
    {
        $pdo = self::getPDO();
        $sql = 'UPDATE Resena 
                SET comentario = :comentario, calificacion = :calificacion, imagen = :imagen 
                WHERE idResena = :idResena';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comentario', $comentario);
        $stmt->bindParam(':calificacion', $calificacion);
        $stmt->bindParam(':imagen', $imagenUrl);
        $stmt->bindParam(':idResena', $id);
        $stmt->execute();

        return true;
    }
}
