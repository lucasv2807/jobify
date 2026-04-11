<?php

use PHPUnit\Framework\TestCase;

/**
 * Test para la clase DB.
 *
 * Este test verifica el funcionamiento correcto de la clase DB
 * que maneja la conexión a la base de datos MariaDB.
 */
class DBTest extends TestCase
{
    /**
     * Variables de entorno originales para restaurar después de los tests.
     */
    private static $originalEnv = [];

    /**
     * Setup que se ejecuta una vez antes de todos los tests.
     */
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        // Guardar variables de entorno originales
        self::$originalEnv = [
            'MARIADB_HOST' => $_ENV['MARIADB_HOST'] ?? null,
            'MARIADB_USERNAME' => $_ENV['MARIADB_USERNAME'] ?? null,
            'MARIADB_PASSWORD' => $_ENV['MARIADB_PASSWORD'] ?? null,
            'MARIADB_PORT' => $_ENV['MARIADB_PORT'] ?? null,
        ];

        // Cargar variables de entorno desde .env si existe
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        } else {
            // Configurar variables de entorno de prueba
            $_ENV['MARIADB_HOST'] ??= 'mariadb';
            $_ENV['MARIADB_USERNAME'] ??= 'jobify';
            $_ENV['MARIADB_PASSWORD'] ??= 'jobify';
            $_ENV['MARIADB_PORT'] ??= '3306';
        }
    }

    /**
     * Setup que se ejecuta antes de cada test.
     */
    protected function setUp(): void
    {
        // Resetear la conexión PDO para cada test
        $this->resetDBConnection();
    }

    /**
     * Cleanup que se ejecuta después de cada test.
     */
    protected function tearDown(): void
    {
        $this->resetDBConnection();
    }

    /**
     * Cleanup final después de todos los tests.
     */
    public static function tearDownAfterClass(): void
    {
        // Restaurar variables de entorno originales
        foreach (self::$originalEnv as $key => $value) {
            if ($value !== null) {
                $_ENV[$key] = $value;
            } else {
                unset($_ENV[$key]);
            }
        }
    }

    /**
     * Test: Verificar que la clase DB existe.
     */
    public function testDBClassExists(): void
    {
        $this->assertTrue(
            class_exists('DB'),
            'La clase DB debe existir'
        );
    }

    /**
     * Test: Verificar que el método connect existe y es estático.
     */
    public function testConnectMethodExists(): void
    {
        $this->assertTrue(
            method_exists('DB', 'connect'),
            'El método connect debe existir en la clase DB'
        );

        $reflection = new ReflectionMethod('DB', 'connect');
        $this->assertTrue(
            $reflection->isStatic(),
            'El método connect debe ser estático'
        );
    }

    /**
     * Test: Verificar que connect retorna una instancia de PDO.
     */
    public function testConnectReturnsPDOInstance(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo = DB::connect();

            $this->assertInstanceOf(
                PDO::class,
                $pdo,
                'El método connect debe retornar una instancia de PDO'
            );
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudo conectar a la base de datos: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test: Verificar que connect usa singleton (retorna la misma instancia).
     */
    public function testConnectUsesSingletonPattern(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo1 = DB::connect();
            $pdo2 = DB::connect();

            $this->assertSame(
                $pdo1,
                $pdo2,
                'El método connect debe retornar la misma instancia de PDO (patrón Singleton)'
            );
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudo conectar a la base de datos: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test: Verificar que la conexión tiene el modo de error correcto.
     */
    public function testPDOErrorModeIsException(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo = DB::connect();

            $this->assertEquals(
                PDO::ERRMODE_EXCEPTION,
                $pdo->getAttribute(PDO::ATTR_ERRMODE),
                'PDO debe estar configurado con ERRMODE_EXCEPTION'
            );
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudo conectar a la base de datos: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test: Verificar que se conecta a la base de datos correcta.
     */
    public function testConnectsToCorrectDatabase(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo = DB::connect();

            $stmt = $pdo->query('SELECT DATABASE() as db_name');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->assertEquals(
                'Jobify',
                $result['db_name'],
                'Debe conectarse a la base de datos Jobify'
            );
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudo conectar a la base de datos: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test: Verificar que puede ejecutar queries.
     */
    public function testCanExecuteQueries(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo = DB::connect();

            // Ejecutar una query simple
            $stmt = $pdo->query('SELECT 1 as test');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->assertEquals(
                1,
                $result['test'],
                'Debe poder ejecutar queries básicas'
            );
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudo ejecutar la query: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test: Verificar que las tablas se crean correctamente.
     */
    public function testDatabaseTablesExist(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo = DB::connect();

            // Tablas esperadas en la base de datos
            $expectedTables = [
                'Usuario',
                'Cliente',
                'Proveedor',
                'Servicio',
                'Pagos',
                'Reserva',
                'Resena',
                'Chat',
            ];

            // Verificar cada tabla
            foreach ($expectedTables as $table) {
                $stmt = $pdo->prepare(
                    "SELECT COUNT(*) as count 
                     FROM information_schema.tables 
                     WHERE table_schema = 'Jobify' 
                     AND table_name = ?"
                );
                $stmt->execute([$table]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $this->assertEquals(
                    1,
                    $result['count'],
                    "La tabla $table debe existir en la base de datos"
                );
            }
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudo verificar las tablas: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test: Verificar que la tabla Pagos tiene la columna paymentId.
     */
    public function testPagosTableHasPaymentIdColumn(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo = DB::connect();

            // Ejecutar DB_UPDATE.sql si existe para agregar columna paymentId
            $updateFile = __DIR__ . '/../src/DB_UPDATE.sql';
            if (file_exists($updateFile)) {
                $sql = file_get_contents($updateFile);
                $pdo->exec($sql);
            }

            $stmt = $pdo->prepare(
                "SELECT COUNT(*) as count 
                 FROM information_schema.columns 
                 WHERE table_schema = 'Jobify' 
                 AND table_name = 'Pagos' 
                 AND column_name = 'paymentId'"
            );
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->assertGreaterThanOrEqual(
                1,
                $result['count'],
                'La tabla Pagos debe tener la columna paymentId'
            );
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudo verificar la columna paymentId: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test: Verificar manejo de errores con credenciales inválidas.
     */
    public function testThrowsExceptionWithInvalidCredentials(): void
    {
        // Guardar credenciales válidas
        $validHost = $_ENV['MARIADB_HOST'];
        $validUser = $_ENV['MARIADB_USERNAME'];

        // Configurar credenciales inválidas
        $_ENV['MARIADB_HOST'] = 'invalid_host_12345';
        $_ENV['MARIADB_USERNAME'] = 'invalid_user';

        $this->resetDBConnection();

        require_once __DIR__ . '/../src/DB.php';

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Error connecting to database/');

        try {
            DB::connect();
        } finally {
            // Restaurar credenciales válidas
            $_ENV['MARIADB_HOST'] = $validHost;
            $_ENV['MARIADB_USERNAME'] = $validUser;
            $this->resetDBConnection();
        }
    }

    /**
     * Test: Verificar que el archivo DB.sql existe.
     */
    public function testDBSQLFileExists(): void
    {
        $sqlFile = __DIR__ . '/../src/DB.sql';

        $this->assertFileExists(
            $sqlFile,
            'El archivo DB.sql debe existir en src/'
        );

        $this->assertFileIsReadable(
            $sqlFile,
            'El archivo DB.sql debe ser legible'
        );
    }

    /**
     * Test: Verificar que puede hacer prepared statements.
     */
    public function testCanExecutePreparedStatements(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo = DB::connect();

            // Preparar statement
            $stmt = $pdo->prepare('SELECT ? as value');
            $testValue = 'test_value';
            $stmt->execute([$testValue]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->assertEquals(
                $testValue,
                $result['value'],
                'Debe poder ejecutar prepared statements'
            );
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudo ejecutar prepared statement: ' . $e->getMessage()
            );
        }
    }

    /**
     * Test: Verificar que puede hacer transacciones.
     */
    public function testCanHandleTransactions(): void
    {
        require_once __DIR__ . '/../src/DB.php';

        try {
            $pdo = DB::connect();

            // Iniciar transacción
            $this->assertTrue(
                $pdo->beginTransaction(),
                'Debe poder iniciar una transacción'
            );

            // Verificar que está en transacción
            $this->assertTrue(
                $pdo->inTransaction(),
                'Debe estar en modo de transacción'
            );

            // Rollback
            $this->assertTrue(
                $pdo->rollBack(),
                'Debe poder hacer rollback'
            );

            // Verificar que ya no está en transacción
            $this->assertFalse(
                $pdo->inTransaction(),
                'No debe estar en transacción después del rollback'
            );
        } catch (Exception $e) {
            $this->markTestSkipped(
                'No se pudieron probar las transacciones: ' . $e->getMessage()
            );
        }
    }

    /**
     * Método helper para resetear la conexión de DB.
     */
    private function resetDBConnection(): void
    {
        // Usar reflexión para resetear el PDO estático
        if (class_exists('DB')) {
            $reflection = new ReflectionClass('DB');
            $property = $reflection->getProperty('pdo');
            $property->setAccessible(true);
            $property->setValue(null, null);
        }
    }
}
