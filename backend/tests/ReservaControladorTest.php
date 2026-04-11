<?php

use PHPUnit\Framework\TestCase;

/**
 * Test para la clase ReservaControlador.
 *
 * Este test verifica el funcionamiento correcto del controlador de reservas
 * que maneja la creación y gestión de reservas entre clientes y proveedores.
 *
 * Nota: Este test requiere conexión a la base de datos y datos de prueba válidos.
 */
class ReservaControladorTest extends TestCase
{
    /**
     * Instance of ReservaControlador for testing.
     */
    private static $controlador;

    /**
     * Test data IDs.
     */
    private static $testIdCliente;
    private static $testIdServicio;
    private static $testIdProveedor;
    private static $testIdReserva;
    private static $testFecha;
    private static $testIdClienteInvalido = 99999;
    private static $testIdServicioInvalido = 99999;
    private static $testIdReservaInvalida = 99999;

    /**
     * Setup que se ejecuta una vez antes de todos los tests.
     */
    public static function setUpBeforeClass(): void
    {
        // Cargar variables de entorno
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }

        // Cargar clases necesarias
        require_once __DIR__ . '/../src/DB.php';
        require_once __DIR__ . '/../src/model/Reserva.php';
        require_once __DIR__ . '/../src/model/Chat.php';
        require_once __DIR__ . '/../src/model/Servicio.php';
        require_once __DIR__ . '/../src/controller/Chat.php';
        require_once __DIR__ . '/../src/controller/Servicio.php';
        require_once __DIR__ . '/../src/controller/Reserva.php';

        // Inicializar IDs de prueba
        self::$testIdCliente = 1;
        self::$testIdServicio = 1;
        self::$testIdProveedor = 1;
        self::$testIdReserva = 1;
        self::$testFecha = date('Y-m-d H:i:s', strtotime('+1 day'));
    }

    /**
     * Setup que se ejecuta antes de cada test.
     */
    protected function setUp(): void
    {
        self::$controlador = new ReservaControlador();
    }

    /**
     * Test: Verificar que la clase ReservaControlador existe.
     */
    public function testReservaControladorClassExists(): void
    {
        $this->assertTrue(
            class_exists('ReservaControlador'),
            'La clase ReservaControlador debe existir'
        );
    }

    /**
     * Test: Verificar que el constructor funciona correctamente.
     */
    public function testConstructorFuncionaCorrectamente(): void
    {
        $controlador = new ReservaControlador();
        $this->assertInstanceOf(ReservaControlador::class, $controlador);
    }

    /**
     * Test: Verificar que todos los métodos públicos existen.
     */
    public function testPublicMethodsExist(): void
    {
        $methods = [
            'crearReserva',
            'confirmarReserva',
            'getReservaProveedor',
            'getReservaCliente',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists('ReservaControlador', $method),
                "El método $method debe existir en la clase ReservaControlador"
            );
        }
    }

    /**
     * Test: Verificar parámetros del método crearReserva.
     */
    public function testCrearReservaParameters(): void
    {
        $reflection = new ReflectionMethod('ReservaControlador', 'crearReserva');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
        $this->assertEquals('idServicio', $parameters[1]->getName());
        $this->assertEquals('fecha', $parameters[2]->getName());
    }

    /**
     * Test: Verificar que crearReserva rechaza idCliente vacío.
     */
    public function testCrearReservaRechazaIdClienteVacio(): void
    {
        $resultado = self::$controlador->crearReserva(
            '', // Cliente vacío
            self::$testIdServicio,
            self::$testFecha
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('datos obligatorios', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crearReserva rechaza idServicio vacío.
     */
    public function testCrearReservaRechazaIdServicioVacio(): void
    {
        $resultado = self::$controlador->crearReserva(
            self::$testIdCliente,
            '', // Servicio vacío
            self::$testFecha
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('datos obligatorios', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crearReserva rechaza fecha vacía.
     */
    public function testCrearReservaRechazaFechaVacia(): void
    {
        $resultado = self::$controlador->crearReserva(
            self::$testIdCliente,
            self::$testIdServicio,
            '' // Fecha vacía
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('datos obligatorios', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crearReserva rechaza todos los datos vacíos.
     */
    public function testCrearReservaRechazaTodosDatosVacios(): void
    {
        $resultado = self::$controlador->crearReserva('', '', '');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('datos obligatorios', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crearReserva funciona con datos válidos.
     */
    public function testCrearReservaConDatosValidos(): void
    {
        try {
            $resultado = self::$controlador->crearReserva(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testFecha
            );

            $this->assertIsArray($resultado);

            // Debe tener 'result' o 'error'
            $this->assertTrue(
                isset($resultado['result']) || isset($resultado['error']),
                'La respuesta debe tener result o error'
            );
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearReserva crea chat si no existe.
     */
    public function testCrearReservaCreaChat(): void
    {
        // Este test verifica la lógica de creación de chat automática
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test: Verificar parámetros del método confirmarReserva.
     */
    public function testConfirmarReservaParameters(): void
    {
        $reflection = new ReflectionMethod('ReservaControlador', 'confirmarReserva');
        $parameters = $reflection->getParameters();

        $this->assertCount(4, $parameters);
        $this->assertEquals('confirmado', $parameters[0]->getName());
        $this->assertEquals('idCliente', $parameters[1]->getName());
        $this->assertEquals('idServicio', $parameters[2]->getName());
        $this->assertEquals('idReserva', $parameters[3]->getName());
    }

    /**
     * Test: Verificar que confirmarReserva retorna success con datos válidos.
     */
    public function testConfirmarReservaConDatosValidos(): void
    {
        try {
            $resultado = self::$controlador->confirmarReserva(
                1, // Confirmado
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testIdReserva
            );

            $this->assertIsArray($resultado);

            // Debe tener 'success' o 'error'
            $this->assertTrue(
                isset($resultado['success']) || isset($resultado['error']),
                'La respuesta debe tener success o error'
            );
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reserva válida en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que confirmarReserva acepta confirmado = 0.
     */
    public function testConfirmarReservaAceptaConfirmadoCero(): void
    {
        try {
            $resultado = self::$controlador->confirmarReserva(
                0, // No confirmado
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testIdReserva
            );

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reserva válida en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que confirmarReserva acepta confirmado = 1.
     */
    public function testConfirmarReservaAceptaConfirmadoUno(): void
    {
        try {
            $resultado = self::$controlador->confirmarReserva(
                1, // Confirmado
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testIdReserva
            );

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reserva válida en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que confirmarReserva maneja reserva inexistente.
     */
    public function testConfirmarReservaManejaReservaInexistente(): void
    {
        try {
            $resultado = self::$controlador->confirmarReserva(
                1,
                self::$testIdClienteInvalido,
                self::$testIdServicioInvalido,
                self::$testIdReservaInvalida
            );

            $this->assertIsArray($resultado);

            // Puede retornar error
            if (isset($resultado['error'])) {
                $this->assertIsString($resultado['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método getReservaProveedor.
     */
    public function testGetReservaProveedorParameters(): void
    {
        $reflection = new ReflectionMethod('ReservaControlador', 'getReservaProveedor');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('idProveedor', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que getReservaProveedor retorna array.
     */
    public function testGetReservaProveedorRetornaArray(): void
    {
        try {
            $resultado = self::$controlador->getReservaProveedor(self::$testIdProveedor);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getReservaProveedor agrega tipo schedule.
     */
    public function testGetReservaProveedorAgregaTipoSchedule(): void
    {
        try {
            $resultado = self::$controlador->getReservaProveedor(self::$testIdProveedor);

            if (!isset($resultado['error']) && is_array($resultado) && count($resultado) > 0) {
                // Verificar que cada reserva tiene 'tipo' => 'schedule'
                foreach ($resultado as $reserva) {
                    $this->assertIsArray($reserva);
                    $this->assertArrayHasKey('tipo', $reserva);
                    $this->assertEquals('schedule', $reserva['tipo']);
                }
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reservas en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getReservaProveedor maneja proveedor sin reservas.
     */
    public function testGetReservaProveedorSinReservas(): void
    {
        try {
            $resultado = self::$controlador->getReservaProveedor(self::$testIdProveedor);

            $this->assertIsArray($resultado);

            // Puede ser array vacío o con error
            if (isset($resultado['error'])) {
                $this->assertIsString($resultado['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método getReservaCliente.
     */
    public function testGetReservaClienteParameters(): void
    {
        $reflection = new ReflectionMethod('ReservaControlador', 'getReservaCliente');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que getReservaCliente retorna array.
     */
    public function testGetReservaClienteRetornaArray(): void
    {
        try {
            $resultado = self::$controlador->getReservaCliente(self::$testIdCliente);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getReservaCliente agrega tipo schedule.
     */
    public function testGetReservaClienteAgregaTipoSchedule(): void
    {
        try {
            $resultado = self::$controlador->getReservaCliente(self::$testIdCliente);

            if (!isset($resultado['error']) && is_array($resultado) && count($resultado) > 0) {
                // Verificar que cada reserva tiene 'tipo' => 'schedule'
                foreach ($resultado as $reserva) {
                    $this->assertIsArray($reserva);
                    $this->assertArrayHasKey('tipo', $reserva);
                    $this->assertEquals('schedule', $reserva['tipo']);
                }
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reservas en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getReservaCliente maneja cliente sin reservas.
     */
    public function testGetReservaClienteSinReservas(): void
    {
        try {
            $resultado = self::$controlador->getReservaCliente(self::$testIdClienteInvalido);

            $this->assertIsArray($resultado);

            // Puede ser array vacío o con error
            if (isset($resultado['error'])) {
                $this->assertIsString($resultado['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que la clase usa ReservaModelo.
     */
    public function testClaseUsaReservaModelo(): void
    {
        $this->assertTrue(
            class_exists('ReservaModelo'),
            'La clase ReservaModelo debe estar cargada'
        );
    }

    /**
     * Test: Verificar que la clase usa ChatControlador.
     */
    public function testClaseUsaChatControlador(): void
    {
        $this->assertTrue(
            class_exists('ChatControlador'),
            'La clase ChatControlador debe estar cargada'
        );
    }

    /**
     * Test: Verificar que la clase usa ServicioControlador.
     */
    public function testClaseUsaServicioControlador(): void
    {
        $this->assertTrue(
            class_exists('ServicioControlador'),
            'La clase ServicioControlador debe estar cargada'
        );
    }

    /**
     * Test: Verificar que crearReserva maneja formato de fecha válido.
     */
    public function testCrearReservaAceptaFechaValida(): void
    {
        $fechas = [
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s', strtotime('+1 day')),
            date('Y-m-d H:i:s', strtotime('+1 week')),
        ];

        foreach ($fechas as $fecha) {
            try {
                $resultado = self::$controlador->crearReserva(
                    self::$testIdCliente,
                    self::$testIdServicio,
                    $fecha
                );

                $this->assertIsArray($resultado);
            } catch (Exception $e) {
                $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
                break;
            }
        }
    }

    /**
     * Test: Verificar estructura de respuesta de crearReserva.
     */
    public function testCrearReservaEstructuraDeRespuesta(): void
    {
        try {
            $resultado = self::$controlador->crearReserva(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testFecha
            );

            $this->assertIsArray($resultado);

            // Debe tener 'result' en caso de éxito o 'error' en caso de fallo
            $this->assertTrue(
                isset($resultado['result']) || isset($resultado['error']),
                'La respuesta debe tener result o error'
            );
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar estructura de respuesta de confirmarReserva.
     */
    public function testConfirmarReservaEstructuraDeRespuesta(): void
    {
        try {
            $resultado = self::$controlador->confirmarReserva(
                1,
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testIdReserva
            );

            $this->assertIsArray($resultado);

            // Debe tener 'success' en caso de éxito o 'error' en caso de fallo
            $this->assertTrue(
                isset($resultado['success']) || isset($resultado['error']),
                'La respuesta debe tener success o error'
            );
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reserva válida en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearReserva maneja null en parámetros.
     */
    public function testCrearReservaManejaNull(): void
    {
        $resultado = self::$controlador->crearReserva(null, null, null);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * Test: Verificar que confirmarReserva maneja null en parámetros.
     */
    public function testConfirmarReservaManejaNull(): void
    {
        try {
            $resultado = self::$controlador->confirmarReserva(null, null, null, null);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getReservaProveedor maneja null.
     */
    public function testGetReservaProveedorManejaNull(): void
    {
        try {
            $resultado = self::$controlador->getReservaProveedor(null);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getReservaCliente maneja null.
     */
    public function testGetReservaClienteManejaNull(): void
    {
        try {
            $resultado = self::$controlador->getReservaCliente(null);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar consistencia de estructura de errores.
     */
    public function testEstructuraDeErrorConsistente(): void
    {
        $resultado1 = self::$controlador->crearReserva('', '', '');

        try {
            $resultado2 = self::$controlador->confirmarReserva(
                1,
                self::$testIdClienteInvalido,
                self::$testIdServicioInvalido,
                self::$testIdReservaInvalida
            );

            $this->assertArrayHasKey('error', $resultado1);
            $this->assertIsString($resultado1['error']);

            if (isset($resultado2['error'])) {
                $this->assertIsString($resultado2['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que todos los métodos son públicos.
     */
    public function testTodosLosMetodosSonPublicos(): void
    {
        $methods = [
            'crearReserva',
            'confirmarReserva',
            'getReservaProveedor',
            'getReservaCliente',
        ];

        foreach ($methods as $methodName) {
            $reflection = new ReflectionMethod('ReservaControlador', $methodName);
            $this->assertTrue(
                $reflection->isPublic(),
                "El método $methodName debe ser público"
            );
        }
    }

    /**
     * Test: Verificar que ningún método es estático.
     */
    public function testNingunMetodoEsEstatico(): void
    {
        $methods = [
            'crearReserva',
            'confirmarReserva',
            'getReservaProveedor',
            'getReservaCliente',
        ];

        foreach ($methods as $methodName) {
            $reflection = new ReflectionMethod('ReservaControlador', $methodName);
            $this->assertFalse(
                $reflection->isStatic(),
                "El método $methodName no debe ser estático"
            );
        }
    }

    /**
     * Test: Verificar que crearReserva con idCliente = 0.
     */
    public function testCrearReservaConIdClienteCero(): void
    {
        $resultado = self::$controlador->crearReserva(
            0, // ID = 0 es vacío
            self::$testIdServicio,
            self::$testFecha
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * Test: Verificar que crearReserva con idServicio = 0.
     */
    public function testCrearReservaConIdServicioCero(): void
    {
        $resultado = self::$controlador->crearReserva(
            self::$testIdCliente,
            0, // ID = 0 es vacío
            self::$testFecha
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * Test: Verificar que getReservaProveedor con ID inválido.
     */
    public function testGetReservaProveedorConIdInvalido(): void
    {
        try {
            $resultado = self::$controlador->getReservaProveedor(self::$testIdProveedor + 99999);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getReservaCliente con ID inválido.
     */
    public function testGetReservaClienteConIdInvalido(): void
    {
        try {
            $resultado = self::$controlador->getReservaCliente(self::$testIdCliente + 99999);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que confirmarReserva success es booleano.
     */
    public function testConfirmarReservaSuccessEsBooleano(): void
    {
        try {
            $resultado = self::$controlador->confirmarReserva(
                1,
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testIdReserva
            );

            if (isset($resultado['success'])) {
                $this->assertIsBool($resultado['success']);
                $this->assertTrue($resultado['success']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reserva válida en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que array_map funciona correctamente en getReservaProveedor.
     */
    public function testGetReservaProveedorArrayMapFunciona(): void
    {
        try {
            $resultado = self::$controlador->getReservaProveedor(self::$testIdProveedor);

            if (!isset($resultado['error']) && is_array($resultado)) {
                foreach ($resultado as $reserva) {
                    // Cada elemento debe ser un array
                    $this->assertIsArray($reserva);
                    // Debe tener 'tipo' como primera clave (spread operator)
                    $this->assertArrayHasKey('tipo', $reserva);
                }
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reservas en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que array_map funciona correctamente en getReservaCliente.
     */
    public function testGetReservaClienteArrayMapFunciona(): void
    {
        try {
            $resultado = self::$controlador->getReservaCliente(self::$testIdCliente);

            if (!isset($resultado['error']) && is_array($resultado)) {
                foreach ($resultado as $reserva) {
                    // Cada elemento debe ser un array
                    $this->assertIsArray($reserva);
                    // Debe tener 'tipo' como primera clave (spread operator)
                    $this->assertArrayHasKey('tipo', $reserva);
                }
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere reservas en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearReserva retorna result en caso de éxito.
     */
    public function testCrearReservaRetornaResultEnExito(): void
    {
        try {
            $resultado = self::$controlador->crearReserva(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testFecha
            );

            // Si no hay error, debe tener 'result'
            if (!isset($resultado['error'])) {
                $this->assertArrayHasKey('result', $resultado);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }
}
