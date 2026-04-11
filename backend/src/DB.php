<?php

class DB
{
    // Creando las variables estáticas para la conexión a la base de datos
    private static $host;
    private static $user;
    private static $pass;
    private static $db;
    private static $port;
    private static $pdo;

    private static function init()
    {
        //Iniciar variables de entorno
        self::$host = $_ENV['MARIADB_HOST'];
        self::$user = $_ENV['MARIADB_USERNAME'];
        self::$pass = $_ENV['MARIADB_PASSWORD'];
        self::$port = $_ENV['MARIADB_PORT'];
        self::$db = 'Jobify';
    }

    // Función para conectar a la base de datos
    public static function connect()
    {
        if (self::$pdo == null) {
            self::init();
            try {
                // 1. Conectar sin base de datos al inicio
                $pdo = new PDO(
                    'mysql:host=' . self::$host . ';port=' . self::$port,
                    self::$user,
                    self::$pass
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // 2. Leer y ejecutar el SQL de creación de la DB desde archivo externo
                $sqlFile = __DIR__ . '/DB.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    $pdo->exec($sql);
                } else {
                    throw new Exception('No se encontró el archivo de creación de la DB: ' . $sqlFile);
                }

                // 3. Guardar la conexión con la base de datos seleccionada
                self::$pdo = new PDO(
                    'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$db,
                    self::$user,
                    self::$pass
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new Exception('Error connecting to database: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
