<?php

require_once 'src/DB.php';
class PagoModelo
{
    public function pagar($idCliente, $idServicio, $idProveedor, $precio, $titulo)
    {
        // Lógica para procesar el pago
        $pdo = DB::connect();
        $sql = 'INSERT INTO Pagos (idCliente, idServicio, idProveedor, fechaPago, precio, titulo) VALUES (:idCliente, :idServicio, :idProveedor, NOW(), :precio, :titulo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->execute();

        return true;
    }

    /**
     * Detecta las columnas reales usadas para los tokens en la tabla Proveedor.
     * Devuelve un array con keys 'access' y 'refresh' apuntando al nombre de columna encontrado.
     * Lanza Exception si no se encuentran columnas compatibles.
     */
    private function resolveTokenColumns(PDO $pdo): array
    {
        $dbName = null;
        try {
            $stmtDb = $pdo->query('SELECT DATABASE()');
            $dbName = $stmtDb->fetchColumn();
        } catch (Exception $e) {
            // Si no podemos conocer la DB, asumimos 'Jobify' (como en DB::connect())
            $dbName = 'Jobify';
        }

        $candidates = [
            'access' => ['accessToken', 'accesToken', 'access_token', 'acces_token'],
            'refresh' => ['refreshToken', 'refresh_token'],
        ];

        $placeholders = implode(',', array_fill(0, count($candidates['access']) + count($candidates['refresh']), '?'));
        $values = array_merge($candidates['access'], $candidates['refresh']);

        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'Proveedor' AND COLUMN_NAME IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $params = array_merge([$dbName], $values);
        $stmt->execute($params);
        $found = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $result = ['access' => null, 'refresh' => null];
        foreach ($candidates['access'] as $col) {
            if (in_array($col, $found, true)) {
                $result['access'] = $col;
                break;
            }
        }
        foreach ($candidates['refresh'] as $col) {
            if (in_array($col, $found, true)) {
                $result['refresh'] = $col;
                break;
            }
        }

        if ($result['access'] === null || $result['refresh'] === null) {
            throw new Exception('No se encontraron columnas de token válidas en la tabla Proveedor. Columnas esperadas: ' . implode(', ', $values));
        }

        return $result;
    }

    public function getHistorialPagos($idCliente)
    {
        // Lógica para obtener el historial de pagos con información detallada
        $pdo = DB::connect();
        $sql = 'SELECT 
                    Pagos.*,
                    Servicio.titulo AS servicioTitulo,
                    Servicio.descripcion AS servicioDescripcion,
                    Usuario.nombre AS nombre,
                    Usuario.apellido AS apellido,
                    Usuario.imagen AS imagen,
                    Proveedor.nombreEmpresa
                FROM Pagos
                JOIN Servicio ON Pagos.idServicio = Servicio.idServicio
                JOIN Proveedor ON Pagos.idProveedor = Proveedor.idProveedor
                JOIN Usuario ON Proveedor.idProveedor = Usuario.idUsuario
                WHERE Pagos.idCliente = :idCliente 
                ORDER BY Pagos.fechaPago DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistorialPagosProveedor($idProveedor)
    {
        // Lógica para obtener el historial de pagos recibidos por un proveedor
        $pdo = DB::connect();
        $sql = 'SELECT 
                    Pagos.*,
                    Servicio.titulo AS servicioTitulo,
                    Servicio.descripcion AS servicioDescripcion,
                    Usuario.nombre AS nombre,
                    Usuario.apellido AS apellido,
                    Usuario.imagen AS imagen
                FROM Pagos
                JOIN Servicio ON Pagos.idServicio = Servicio.idServicio
                JOIN Cliente ON Pagos.idCliente = Cliente.idCliente
                JOIN Usuario ON Cliente.idCliente = Usuario.idUsuario
                WHERE Pagos.idProveedor = :idProveedor 
                ORDER BY Pagos.fechaPago DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardarToken($idProveedor, $accessToken, $refreshToken, $publicKey = null)
    {
        $pdo = DB::connect();
        $cols = $this->resolveTokenColumns($pdo);

        // Si se proporciona publicKey, incluirlo en el UPDATE
        if ($publicKey !== null) {
            $sql = 'UPDATE Proveedor SET `' . $cols['access'] . '` = :accessToken, `' . $cols['refresh'] . '` = :refreshToken, `publicKey` = :publicKey WHERE idProveedor = :idProveedor';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':publicKey', $publicKey);
        } else {
            $sql = 'UPDATE Proveedor SET `' . $cols['access'] . '` = :accessToken, `' . $cols['refresh'] . '` = :refreshToken WHERE idProveedor = :idProveedor';
            $stmt = $pdo->prepare($sql);
        }

        $stmt->bindParam(':accessToken', $accessToken);
        $stmt->bindParam(':refreshToken', $refreshToken);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();
    }

    public function obtenerTokens($idProveedor)
    {
        $pdo = DB::connect();
        // Usar accesToken (nombre real en BD) y devolverlo con el mismo nombre
        $sql = 'SELECT accesToken, refreshToken, publicKey FROM Proveedor WHERE idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarAccessToken($idProveedor, $newAccessToken)
    {
        $pdo = DB::connect();
        $cols = $this->resolveTokenColumns($pdo);
        $sql = 'UPDATE Proveedor SET `' . $cols['access'] . '` = :accessToken WHERE idProveedor = :idProveedor';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':accessToken', $newAccessToken);
        $stmt->bindParam(':idProveedor', $idProveedor);
        $stmt->execute();
    }

    public function obtenerPagoConPaymentId($idCliente, $idServicio, $fechaPago)
    {
        // Obtener el pago con su paymentId y datos del proveedor
        $pdo = DB::connect();
        $sql = 'SELECT Pagos.*, Proveedor.accesToken 
                FROM Pagos 
                JOIN Proveedor ON Pagos.idProveedor = Proveedor.idProveedor
                WHERE Pagos.idCliente = :idCliente 
                AND Pagos.idServicio = :idServicio 
                AND Pagos.fechaPago = :fechaPago';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':fechaPago', $fechaPago);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadoPago($idCliente, $idServicio, $fechaPago, $estado)
    {
        // Actualizar el estado del pago
        $pdo = DB::connect();
        $sql = 'UPDATE Pagos SET estado = :estado WHERE idCliente = :idCliente AND idServicio = :idServicio AND fechaPago = :fechaPago';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':fechaPago', $fechaPago);
        $stmt->execute();

        return true;
    }

    public function guardarPaymentId($idCliente, $idServicio, $fechaPago, $paymentId)
    {
        // Guardar el paymentId de MercadoPago
        $pdo = DB::connect();
        $sql = 'UPDATE Pagos SET paymentId = :paymentId WHERE idCliente = :idCliente AND idServicio = :idServicio AND fechaPago = :fechaPago';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':paymentId', $paymentId);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->bindParam(':fechaPago', $fechaPago);
        $stmt->execute();

        return true;
    }

    public function verificarContratacion($idCliente, $idServicio)
    {
        // Verificar si existe al menos un pago completado para este cliente y servicio
        $pdo = DB::connect();
        $sql = 'SELECT COUNT(*) as total FROM Pagos WHERE idCliente = :idCliente AND idServicio = :idServicio AND estado = "completado"';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':idServicio', $idServicio);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total'] > 0;
    }
}
