<?php

require_once 'src/DB.php';
class UsuarioModelo
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

    // Metodo para crear un usuario e insertarlo en la base de datos
    public static function crearUsuario($nombre, $apellido, $email, $password, $fecha_nacimiento, $foto_perfil, $telefono)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Usuario (nombre, apellido, email, contraseña, fechaNac, imagen) VALUES (:nombre, :apellido, :email, :password, :fecha_nacimiento, :foto_perfil)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindParam(':foto_perfil', $foto_perfil);
        $stmt->execute();
        $idUsuario = $pdo->lastInsertId();
        // Llamar al metodo para crear un telefono de usuario y asignarlo al usuario creado
        UsuarioModelo::crearTelefonoUsuario($idUsuario, $telefono);

        return $idUsuario;
    }

    public static function crearCliente($idUsuario)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Cliente (idCliente) VALUES (:idUsuario)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();

        // No usar lastInsertId porque no hay auto_increment
        return true;
    }

    // Metodo para crear un usuario proveedor e insertarlo en la base de datos
    public static function crearUsuarioProveedor($nombre, $apellido, $email, $password, $fecha_nacimiento, $foto_perfil, $telefono, $nombreEmpresa, $descripcion, $experiencia, $habilidades, $titulos)
    {
        // llamar al metodo para crear un usuario normal y luego insertar los datos del proveedor
        $idProveedor = UsuarioModelo::crearUsuario($nombre, $apellido, $email, $password, $fecha_nacimiento, $foto_perfil, $telefono);
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Proveedor (idProveedor, nombreEmpresa, descripcion, experiencia) VALUES (:idProveedor, :nombreEmpresa, :descripcion, :experiencia)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->bindParam(':nombreEmpresa', $nombreEmpresa);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':experiencia', $experiencia);
        $stmt->execute();
        // Iterar sobre habilidades y títulos si son arrays
        if (is_array($habilidades)) {
            foreach ($habilidades as $habilidad) {
                UsuarioModelo::crearProveedorHabilidad($idProveedor, $habilidad);
            }
        } elseif (!empty($habilidades)) {
            UsuarioModelo::crearProveedorHabilidad($idProveedor, $habilidades);
        }
        if (is_array($titulos)) {
            foreach ($titulos as $titulo) {
                UsuarioModelo::CrearProveedorTitulo($idProveedor, $titulo);
            }
        } elseif (!empty($titulos)) {
            UsuarioModelo::CrearProveedorTitulo($idProveedor, $titulos);
        }

        return $idProveedor;
    }

    // Metodo para crear un Telefono de usuario e insertarlo en la base de datos
    public static function crearTelefonoUsuario($idUsuario, $telefono)
    {
        if (empty($telefono)) {
            return null;
        }
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Usuario_Telefono (idUsuario, telefono) VALUES (:idUsuario, :telefono)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->execute();

        // No usar lastInsertId porque no hay auto_increment
        return true;
    }

    // Metodo para crear una habilidad de proveedor e insertarlo en la base de datos
    public static function crearProveedorHabilidad($idProveedor, $habilidad)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Proveedor_Habilidad (idProveedor, habilidad) VALUES (:idProveedor, :habilidad)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->bindParam(':habilidad', $habilidad);
        $stmt->execute();

        // No usar lastInsertId porque no hay auto_increment
        return true;
    }

    // Metodo para crear un titulo de proveedor e insertarlo en la base de datos
    public static function CrearProveedorTitulo($idProveedor, $titulo)
    {
        $pdo = self::getPDO();
        $sql = 'INSERT INTO Proveedor_Titulo (idProveedor, titulo) VALUES (:idProveedor, :titulo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->execute();

        // No usar lastInsertId porque no hay auto_increment
        return true;
    }

    // Metodo para obtener un usuario por email haciendole un select a la base de datos
    public static function getUsuarioByEmail($email)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * 
        FROM Usuario 
        WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario;
    }

    // Metodo para obtener un usuario por ID haciendo un select a la base de datos
    public static function getUsuarioById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Usuario WHERE idUsuario = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario;
    }

    public static function getUsuarioTelefonoById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Usuario_Telefono WHERE idUsuario = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $telefono = $stmt->fetch(PDO::FETCH_ASSOC);

        return $telefono;
    }

    public static function getProveedorById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Proveedor WHERE idProveedor = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

        return $proveedor;
    }

    public static function getProveedorHabilidadesById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Proveedor_Habilidad WHERE idProveedor = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $habilidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $habilidades;
    }

    public static function getProveedorTitulosById($id)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT * FROM Proveedor_Titulo WHERE idProveedor = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $titulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $titulos;
    }

    public static function updateUsuario($idUsuario, $nombre, $apellido, $email, $foto_perfil, $telefono)
    {
        $pdo = self::getPDO();
        $sql = 'UPDATE Usuario SET nombre = :nombre, apellido = :apellido, email = :email, imagen = :foto_perfil WHERE idUsuario = :idUsuario';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':foto_perfil', $foto_perfil);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();

        self::crearTelefonoUsuario($idUsuario, $telefono);

        return true;
    }

    public static function eliminarTelefonoUsuario($idUsuario)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Usuario_Telefono WHERE idUsuario = :idUsuario';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();

        return true;
    }

    public static function eliminarProveedorHabilidades($idProveedor)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Proveedor_Habilidad WHERE idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();

        return true;
    }

    public static function updateProveedor($idProveedor, $nombreEmpresa, $descripcion, $experiencia)
    {
        $pdo = self::getPDO();
        $sql = 'UPDATE Proveedor SET nombreEmpresa = :nombreEmpresa, descripcion = :descripcion, experiencia = :experiencia WHERE idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombreEmpresa', $nombreEmpresa);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':experiencia', $experiencia);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $result = $stmt->execute();

        // Log para debug
        error_log('SQL Update Proveedor - Rows affected: ' . $stmt->rowCount() . ", SQL: $sql");
        error_log("Params - idProveedor: $idProveedor, nombreEmpresa: $nombreEmpresa, descripcion: $descripcion, experiencia: $experiencia");

        return $result && $stmt->rowCount() >= 0;
    }

    public static function eliminarProveedorTitulos($idProveedor)
    {
        $pdo = self::getPDO();
        $sql = 'DELETE FROM Proveedor_Titulo WHERE idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();

        return true;
    }

    public static function updateUsuarioPassword($idUsuario, $nuevoPasswordHash)
    {
        $pdo = self::getPDO();
        $sql = 'UPDATE Usuario SET contraseña = :nuevoPassword WHERE idUsuario = :idUsuario';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nuevoPassword', $nuevoPasswordHash);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();

        return true;
    }

    public static function esCliente($idUsuario)
    {
        $pdo = self::getPDO();
        $sql = 'SELECT COUNT(*) FROM Cliente WHERE idCliente = :idUsuario';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        return $count > 0;
    }

    /**
     * Calcular el promedio de calificaciones de todos los servicios de un proveedor
     * 
     * @param int $idProveedor ID del proveedor
     * @return array Array con 'promedio' y 'totalResenas'
     */
    public static function getPromedioCalificacionProveedor($idProveedor)
    {
        $pdo = self::getPDO();
        
        // Query para obtener el promedio de calificaciones de todos los servicios del proveedor
        // y el número total de reseñas
        $sql = 'SELECT 
                    COALESCE(AVG(r.calificacion), 0) as promedio,
                    COUNT(r.idResena) as totalResenas
                FROM Servicio s
                LEFT JOIN Publica p ON s.idServicio = p.idServicio
                LEFT JOIN Resena r ON p.idResena = r.idResena
                WHERE s.idProveedor = :idProveedor';
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'promedio' => round(floatval($resultado['promedio']), 2),
            'totalResenas' => intval($resultado['totalResenas'])
        ];
    }
}
