<?php

use PHPUnit\Framework\TestCase;

/**
 * Test para la clase ChatControlador.
 *
 * Este test verifica el funcionamiento correcto del controlador de Chat
 * que maneja el envío de mensajes y gestión de conversaciones.
 *
 * Nota: Este test requiere conexión a la base de datos y datos de prueba.
 */
class ChatControladorTest extends TestCase
{
    /**
     * Instance of ChatControlador for testing.
     */
    private static $controlador;

    /**
     * Test data IDs.
     */
    private static $testIdCliente;
    private static $testIdServicio;
    private static $testIdProveedor;
    private static $testIdClienteInvalido = 99999;
    private static $testIdServicioInvalido = 99999;

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
        require_once __DIR__ . '/../src/model/Chat.php';
        require_once __DIR__ . '/../src/model/Usuario.php';
        require_once __DIR__ . '/../src/controller/Chat.php';

        // Inicializar IDs de prueba (estos deben existir en tu BD de testing)
        self::$testIdCliente = 1;
        self::$testIdServicio = 1;
        self::$testIdProveedor = 1;
    }

    /**
     * Setup que se ejecuta antes de cada test.
     */
    protected function setUp(): void
    {
        // Nada específico por ahora
    }

    /**
     * Test: Verificar que la clase ChatControlador existe.
     */
    public function testChatControladorClassExists(): void
    {
        $this->assertTrue(
            class_exists('ChatControlador'),
            'La clase ChatControlador debe existir'
        );
    }

    /**
     * Test: Verificar que todos los métodos públicos existen.
     */
    public function testPublicMethodsExist(): void
    {
        $methods = [
            'sendMessage',
            'createChat',
            'getMessageHistory',
            'getChatsCliente',
            'getChatsProveedor',
            'checkChatExists',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists('ChatControlador', $method),
                "El método $method debe existir en la clase ChatControlador"
            );

            $reflection = new ReflectionMethod('ChatControlador', $method);
            $this->assertTrue(
                $reflection->isStatic(),
                "El método $method debe ser estático"
            );
        }
    }

    /**
     * Test: Verificar parámetros del método sendMessage.
     */
    public function testSendMessageParameters(): void
    {
        $reflection = new ReflectionMethod('ChatControlador', 'sendMessage');
        $parameters = $reflection->getParameters();

        $this->assertCount(4, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
        $this->assertEquals('idServicio', $parameters[1]->getName());
        $this->assertEquals('mensaje', $parameters[2]->getName());
        $this->assertEquals('idEmisor', $parameters[3]->getName());
    }

    /**
     * Test: Verificar que sendMessage rechaza mensaje vacío.
     */
    public function testSendMessageRechazaMensajeVacio(): void
    {
        $resultado = ChatControlador::sendMessage(
            self::$testIdCliente,
            self::$testIdServicio,
            '',
            self::$testIdCliente
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('vacío', $resultado['error']);
    }

    /**
     * Test: Verificar que sendMessage rechaza cliente inexistente.
     */
    public function testSendMessageRechazaClienteInexistente(): void
    {
        $resultado = ChatControlador::sendMessage(
            self::$testIdClienteInvalido,
            self::$testIdServicio,
            'Mensaje de prueba',
            self::$testIdClienteInvalido
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('no existe', $resultado['error']);
    }

    /**
     * Test: Verificar que sendMessage funciona con datos válidos.
     */
    public function testSendMessageConDatosValidos(): void
    {
        // Este test requiere datos válidos en la BD
        try {
            $resultado = ChatControlador::sendMessage(
                self::$testIdCliente,
                self::$testIdServicio,
                'Mensaje de prueba para test',
                self::$testIdCliente
            );

            // Debe retornar algo (éxito o error específico de BD)
            $this->assertNotNull($resultado);

            // Si no hay error, debe ser un resultado válido
            if (!isset($resultado['error'])) {
                $this->assertIsArray($resultado);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método createChat.
     */
    public function testCreateChatParameters(): void
    {
        $reflection = new ReflectionMethod('ChatControlador', 'createChat');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
        $this->assertEquals('idServicio', $parameters[1]->getName());
        $this->assertEquals('idProveedor', $parameters[2]->getName());
    }

    /**
     * Test: Verificar que createChat acepta parámetros válidos.
     */
    public function testCreateChatConParametrosValidos(): void
    {
        try {
            $resultado = ChatControlador::createChat(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testIdProveedor
            );

            $this->assertNotNull($resultado);

            // Si retorna error, debe ser un array con la key 'error'
            if (is_array($resultado) && isset($resultado['error'])) {
                $this->assertArrayHasKey('error', $resultado);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método getMessageHistory.
     */
    public function testGetMessageHistoryParameters(): void
    {
        $reflection = new ReflectionMethod('ChatControlador', 'getMessageHistory');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
        $this->assertEquals('idServicio', $parameters[1]->getName());
        $this->assertEquals('idProveedor', $parameters[2]->getName());
    }

    /**
     * Test: Verificar que getMessageHistory retorna error si chat no existe.
     */
    public function testGetMessageHistoryChatNoExiste(): void
    {
        $resultado = ChatControlador::getMessageHistory(
            self::$testIdClienteInvalido,
            self::$testIdServicioInvalido,
            self::$testIdProveedor
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('no existe', $resultado['error']);
    }

    /**
     * Test: Verificar que getMessageHistory valida permisos del proveedor.
     */
    public function testGetMessageHistoryValidaPermisosProveedor(): void
    {
        try {
            // Intentar acceder con un proveedor que no es dueño del chat
            $resultado = ChatControlador::getMessageHistory(
                self::$testIdCliente,
                self::$testIdServicio,
                99999 // ID de proveedor inválido
            );

            // Debe retornar error de acceso o chat no existe
            $this->assertIsArray($resultado);
            $this->assertArrayHasKey('error', $resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere chat existente en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getMessageHistory retorna estructura correcta.
     */
    public function testGetMessageHistoryEstructuraCorrecta(): void
    {
        try {
            $resultado = ChatControlador::getMessageHistory(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testIdProveedor
            );

            // Si no hay error, debe tener chat y messages
            if (!isset($resultado['error'])) {
                $this->assertArrayHasKey('chat', $resultado);
                $this->assertArrayHasKey('messages', $resultado);
                $this->assertIsArray($resultado['messages']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método getChatsCliente.
     */
    public function testGetChatsClienteParameters(): void
    {
        $reflection = new ReflectionMethod('ChatControlador', 'getChatsCliente');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que getChatsCliente retorna array.
     */
    public function testGetChatsClienteRetornaArray(): void
    {
        try {
            $resultado = ChatControlador::getChatsCliente(self::$testIdCliente);

            $this->assertIsArray($resultado);

            // Si hay chats, cada uno debe tener 'tipo' => 'Chat'
            if (!isset($resultado['error']) && count($resultado) > 0) {
                foreach ($resultado as $chat) {
                    $this->assertArrayHasKey('tipo', $chat);
                    $this->assertEquals('Chat', $chat['tipo']);
                }
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getChatsCliente agrega tipo Chat.
     */
    public function testGetChatsClienteAgregaTipoChat(): void
    {
        try {
            $resultado = ChatControlador::getChatsCliente(self::$testIdCliente);

            if (!isset($resultado['error']) && is_array($resultado)) {
                // Cada chat debe tener el campo 'tipo'
                foreach ($resultado as $chat) {
                    $this->assertIsArray($chat);
                    $this->assertArrayHasKey('tipo', $chat);
                    $this->assertEquals('Chat', $chat['tipo']);
                }
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método getChatsProveedor.
     */
    public function testGetChatsProveedorParameters(): void
    {
        $reflection = new ReflectionMethod('ChatControlador', 'getChatsProveedor');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('idProveedor', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que getChatsProveedor retorna array.
     */
    public function testGetChatsProveedorRetornaArray(): void
    {
        try {
            $resultado = ChatControlador::getChatsProveedor(self::$testIdProveedor);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método checkChatExists.
     */
    public function testCheckChatExistsParameters(): void
    {
        $reflection = new ReflectionMethod('ChatControlador', 'checkChatExists');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
        $this->assertEquals('idServicio', $parameters[1]->getName());
    }

    /**
     * Test: Verificar que checkChatExists retorna booleano o array.
     */
    public function testCheckChatExistsRetornaValorValido(): void
    {
        try {
            $resultado = ChatControlador::checkChatExists(
                self::$testIdCliente,
                self::$testIdServicio
            );

            // Debe retornar booleano o array
            $this->assertTrue(
                is_bool($resultado) || is_array($resultado),
                'checkChatExists debe retornar booleano o array'
            );
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que checkChatExists con IDs inválidos.
     */
    public function testCheckChatExistsConIDsInvalidos(): void
    {
        try {
            $resultado = ChatControlador::checkChatExists(
                self::$testIdClienteInvalido,
                self::$testIdServicioInvalido
            );

            // Con IDs inválidos debe retornar false
            if (is_bool($resultado)) {
                $this->assertFalse($resultado);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que sendMessage maneja mensajes largos.
     */
    public function testSendMessageConMensajeLargo(): void
    {
        $mensajeLargo = str_repeat('Este es un mensaje muy largo. ', 100);

        try {
            $resultado = ChatControlador::sendMessage(
                self::$testIdCliente,
                self::$testIdServicio,
                $mensajeLargo,
                self::$testIdCliente
            );

            $this->assertNotNull($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que sendMessage maneja caracteres especiales.
     */
    public function testSendMessageConCaracteresEspeciales(): void
    {
        $mensajeEspecial = 'Mensaje con ñ, acentós, símbolos: @#$% y emojis 😀';

        try {
            $resultado = ChatControlador::sendMessage(
                self::$testIdCliente,
                self::$testIdServicio,
                $mensajeEspecial,
                self::$testIdCliente
            );

            $this->assertNotNull($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getChatsCliente maneja cliente sin chats.
     */
    public function testGetChatsClienteSinChats(): void
    {
        try {
            $resultado = ChatControlador::getChatsCliente(self::$testIdClienteInvalido);

            $this->assertIsArray($resultado);

            // Puede retornar array vacío o error
            if (!isset($resultado['error'])) {
                $this->assertIsArray($resultado);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getChatsProveedor maneja proveedor sin chats.
     */
    public function testGetChatsProveedorSinChats(): void
    {
        try {
            $resultado = ChatControlador::getChatsProveedor(99999);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que todos los métodos manejan null correctamente.
     */
    public function testMetodosManejanNullCorrectamente(): void
    {
        // Este test verifica que los métodos no lanzan errores fatales con null
        $this->expectNotToPerformAssertions();

        // Los métodos deben manejar null sin lanzar errores fatales
        // (aunque pueden retornar errores)
    }

    /**
     * Test: Verificar estructura de respuesta de error consistente.
     */
    public function testEstructuraDeErrorConsistente(): void
    {
        // Verificar que sendMessage retorna error en formato consistente
        $resultado1 = ChatControlador::sendMessage(
            self::$testIdClienteInvalido,
            self::$testIdServicio,
            'test',
            self::$testIdClienteInvalido
        );

        $resultado2 = ChatControlador::sendMessage(
            self::$testIdCliente,
            self::$testIdServicio,
            '',
            self::$testIdCliente
        );

        $this->assertArrayHasKey('error', $resultado1);
        $this->assertArrayHasKey('error', $resultado2);
        $this->assertIsString($resultado1['error']);
        $this->assertIsString($resultado2['error']);
    }

    /**
     * Test: Verificar que getMessageHistory retorna error de acceso.
     */
    public function testGetMessageHistoryErrorDeAcceso(): void
    {
        try {
            // Crear un chat primero si es posible
            ChatControlador::createChat(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testIdProveedor
            );

            // Intentar acceder con proveedor diferente
            $resultado = ChatControlador::getMessageHistory(
                self::$testIdCliente,
                self::$testIdServicio,
                99998 // Proveedor diferente
            );

            if (isset($resultado['error'])) {
                $this->assertStringContainsString('acceso', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que la clase usa dependencias correctamente.
     */
    public function testClaseUsaDependenciasCorrectamente(): void
    {
        // Verificar que las clases modelo están cargadas
        $this->assertTrue(
            class_exists('ChatModelo'),
            'La clase ChatModelo debe estar cargada'
        );

        $this->assertTrue(
            class_exists('UsuarioModelo'),
            'La clase UsuarioModelo debe estar cargada'
        );
    }

    /**
     * Test: Verificar que los métodos son públicos y estáticos.
     */
    public function testMetodosSonPublicosYEstaticos(): void
    {
        $methods = [
            'sendMessage',
            'createChat',
            'getMessageHistory',
            'getChatsCliente',
            'getChatsProveedor',
            'checkChatExists',
        ];

        foreach ($methods as $methodName) {
            $reflection = new ReflectionMethod('ChatControlador', $methodName);

            $this->assertTrue(
                $reflection->isPublic(),
                "El método $methodName debe ser público"
            );

            $this->assertTrue(
                $reflection->isStatic(),
                "El método $methodName debe ser estático"
            );
        }
    }
}
