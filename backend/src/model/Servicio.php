<?php

require_once 'src/DB.php';

class ServicioModelo
{
    protected static function getPDO()
    {
        try {
            return DB::connect();
        } catch (PDOException $e) {
            throw new Exception('Error de conexión a la base de datos');
        }
    }

    public static function crearServicio($idProveedor, $nombre, $descripcion, $precio)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Servicio (idProveedor, titulo, descripcion, precio) VALUES (:idProveedor, :titulo, :descripcion, :precio)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->bindParam(':titulo', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->execute();
        $idServicio = $pdo->lastInsertId();

        return $idServicio;
    }

    public static function crearCategorias($idServicio, $categoria)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Servicio_Categoria (idServicio, categoria) VALUES (:idServicio, :categoria)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->execute();

        return $pdo->lastInsertId();
    }

    public static function crearDiasDisponibles($idServicio, $diaDisponible)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Servicio_DiaDisponible (idServicio, diaDisponible) VALUES (:idServicio, :dia)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':dia', $diaDisponible);
        $stmt->execute();

        return $pdo->lastInsertId();
    }

    public static function crearFotoServicio($idServicio, $fotoServicio)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Servicio_Imagen (idServicio, imagen) VALUES (:idServicio, :fotoServicio)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':fotoServicio', $fotoServicio);
        $stmt->execute();

        return $pdo->lastInsertId();
    }

    public static function crearUbicacion($idServicio, $ubicacion)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Servicio_Ubicacion (idServicio, ubicacion) VALUES (:idServicio, :ubicacion)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':ubicacion', $ubicacion);
        $stmt->execute();

        return $pdo->lastInsertId();
    }

    public static function getServicioById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT *, (SELECT nombre FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS nombreProveedor,
                       (SELECT apellido FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS apellidoProveedor,
                       (SELECT imagen FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS imagenProveedor
        FROM Servicio WHERE idServicio = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

        return $servicio;
    }

    public static function getDiasDisponiblesById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Servicio_DiaDisponible WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $id);
        $stmt->execute();
        $diasDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $diasDisponibles;
    }

    public static function getCategoriasById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Servicio_Categoria WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $id);
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $categorias;
    }

    public static function getUbicacionesById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Servicio_Ubicacion WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $id);
        $stmt->execute();
        $ubicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $ubicaciones;
    }

    public static function getServicios()
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Servicio';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $servicios;
    }

    public static function getImagenesById($idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Servicio_Imagen WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();
        $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $imagenes;
    }

    public static function borrarServicio($idServicio, $idProveedor)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Servicio WHERE idServicio = :idServicio AND idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();
        $deleted = $stmt->rowCount();
    
        if ($deleted > 0) {
            return ['success' => 'Servicio borrado correctamente'];
        }

        return ['error' => 'No se encontró el servicio o no tienes permisos para borrarlo'];
    }

    public static function searchServicios($query)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Servicio WHERE titulo LIKE :query OR descripcion LIKE :query';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public static function getServiciosByProveedor($idProveedor)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT Servicio.*, 
                       (SELECT nombre FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS nombreProveedor,
                       (SELECT apellido FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS apellidoProveedor,
                       (SELECT imagen FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS imagenProveedor
                FROM Servicio 
                WHERE idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $servicios;
    }

    public static function actualizarServicio($idServicio, $nombre, $descripcion, $precio)
    {
        $pdo = self::getPDO();
        $sql = 'UPDATE Servicio SET titulo = :titulo, descripcion = :descripcion, precio = :precio WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titulo', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();

        return ['success' => 'Servicio actualizado correctamente'];
    }

    public static function eliminarCategorias($idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Servicio_Categoria WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();

        return ['success' => 'Categorías eliminadas correctamente'];
    }

    public static function eliminarDiasDisponibles($idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Servicio_DiaDisponible WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();

        return ['success' => 'Días disponibles eliminados correctamente'];
    }

    public static function eliminarUbicaciones($idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Servicio_Ubicacion WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();

        return ['success' => 'Ubicaciones eliminadas correctamente'];
    }

    public static function eliminarImagenes($idServicio)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Servicio_Imagen WHERE idServicio = :idServicio';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();

        return ['success' => 'Imágenes eliminadas correctamente'];
    }

    public static function saveFavorito($idServicio, $idCliente)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Cliente_Favorito (servicioFavorito, idCliente) VALUES (:idServicio, :idCliente)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->execute();

        return true;
    }

    public static function deleteFavorito($idServicio, $idCliente)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Cliente_Favorito WHERE servicioFavorito = :idServicio AND idCliente = :idCliente';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->execute();

        return true;
    }

    public static function favoritoExists($idServicio, $idCliente)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT COUNT(*) FROM Cliente_Favorito WHERE servicioFavorito = :idServicio AND idCliente = :idCliente';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        return $count > 0;
    }

    public static function getFavoritosByCliente($idCliente)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT Servicio.*, 
                       (SELECT nombre FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS nombreProveedor,
                       (SELECT apellido FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS apellidoProveedor,
                       (SELECT imagen FROM Usuario WHERE Usuario.idUsuario = Servicio.idProveedor) AS imagenProveedor
                FROM Servicio 
                JOIN Cliente_Favorito ON Servicio.idServicio = Cliente_Favorito.servicioFavorito
                WHERE Cliente_Favorito.idCliente = :idCliente';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->execute();
        $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $favoritos;
    }
}
