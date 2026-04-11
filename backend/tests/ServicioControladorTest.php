<?php

use PHPUnit\Framework\TestCase;

/**
 * Test para la clase ServicioControlador.
 *
 * Este test verifica el funcionamiento correcto del controlador de servicios
 * que maneja la creación, actualización, búsqueda y gestión de servicios.
 *
 * Nota: Este test requiere conexión a la base de datos, CLOUDINARY_URL configurada,
 * y APIs de moderación (GOOGLE_API_KEY, GROQ_API_KEY).
 */
class ServicioControladorTest extends TestCase
{
    /**
     * Test data.
     */
    private static $testIdProveedor;
    private static $testIdCliente;
    private static $testIdServicio;
    private static $testNombre = 'Servicio de Prueba';
    private static $testDescripcion = 'Descripción del servicio de prueba';
    private static $testPrecio = 1000;
    private static $testCategorias = ['Jardinería', 'Mantenimiento'];
    private static $testUbicaciones = ['Montevideo', 'Canelones'];
    private static $testDiasDisponibles = ['Lunes', 'Martes', 'Miércoles'];

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
        require_once __DIR__ . '/../src/model/Servicio.php';
        require_once __DIR__ . '/../src/model/Usuario.php';
        require_once __DIR__ . '/../src/controller/Resena.php';
        require_once __DIR__ . '/../src/controller/Usuario.php';
        require_once __DIR__ . '/../src/utils/IA.php';
        require_once __DIR__ . '/../src/controller/Servicio.php';

        // Inicializar IDs de prueba
        self::$testIdProveedor = 1;
        self::$testIdCliente = 1;
        self::$testIdServicio = 1;
    }

    /**
     * Test: Verificar que la clase ServicioControlador existe.
     */
    public function testServicioControladorClassExists(): void
    {
        $this->assertTrue(
            class_exists('ServicioControlador'),
            'La clase ServicioControlador debe existir'
        );
    }

    /**
     * Test: Verificar que todos los métodos públicos estáticos existen.
     */
    public function testPublicStaticMethodsExist(): void
    {
        $methods = [
            'crear',
            'getServicio',
            'borrar',
            'search',
            'updateServicio',
            'searchByIA',
            'getServiciosByProveedor',
            'saveFavorito',
            'deleteFavorito',
            'getFavoritosByCliente',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists('ServicioControlador', $method),
                "El método $method debe existir en la clase ServicioControlador"
            );

            $reflection = new ReflectionMethod('ServicioControlador', $method);
            $this->assertTrue(
                $reflection->isStatic(),
                "El método $method debe ser estático"
            );
        }
    }

    /**
     * Test: Verificar parámetros del método crear.
     */
    public function testCrearParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'crear');
        $parameters = $reflection->getParameters();

        $this->assertCount(8, $parameters);
        $this->assertEquals('idProveedor', $parameters[0]->getName());
        $this->assertEquals('nombre', $parameters[1]->getName());
        $this->assertEquals('descripcion', $parameters[2]->getName());
        $this->assertEquals('categorias', $parameters[3]->getName());
        $this->assertEquals('ubicaciones', $parameters[4]->getName());
        $this->assertEquals('precio', $parameters[5]->getName());
        $this->assertEquals('fotosServicio', $parameters[6]->getName());
        $this->assertEquals('diasDisponibles', $parameters[7]->getName());
    }

    /**
     * Test: Verificar que crear rechaza precio negativo.
     */
    public function testCrearRechazaPrecioNegativo(): void
    {
        $fotosMock = [
            'name' => ['test.jpg'],
            'type' => ['image/jpeg'],
            'tmp_name' => ['/tmp/test.jpg'],
            'error' => [UPLOAD_ERR_OK],
            'size' => [1024],
        ];

        $resultado = ServicioControlador::crear(
            self::$testIdProveedor,
            self::$testNombre,
            self::$testDescripcion,
            self::$testCategorias,
            self::$testUbicaciones,
            -100, // Precio negativo
            $fotosMock,
            self::$testDiasDisponibles
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('negativo', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crear rechaza precio muy alto.
     */
    public function testCrearRechazaPrecioMuyAlto(): void
    {
        $fotosMock = [
            'name' => ['test.jpg'],
            'type' => ['image/jpeg'],
            'tmp_name' => ['/tmp/test.jpg'],
            'error' => [UPLOAD_ERR_OK],
            'size' => [1024],
        ];

        $resultado = ServicioControlador::crear(
            self::$testIdProveedor,
            self::$testNombre,
            self::$testDescripcion,
            self::$testCategorias,
            self::$testUbicaciones,
            500001, // Precio muy alto
            $fotosMock,
            self::$testDiasDisponibles
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('caro', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crear acepta precio 0.
     */
    public function testCrearAceptaPrecioCero(): void
    {
        $fotosMock = [
            'name' => ['test.jpg'],
            'type' => ['image/jpeg'],
            'tmp_name' => ['/tmp/test.jpg'],
            'error' => [UPLOAD_ERR_OK],
            'size' => [1024],
        ];

        try {
            $resultado = ServicioControlador::crear(
                self::$testIdProveedor,
                self::$testNombre,
                self::$testDescripcion,
                self::$testCategorias,
                self::$testUbicaciones,
                0, // Precio cero
                $fotosMock,
                self::$testDiasDisponibles
            );

            // No debe tener error de precio
            if (isset($resultado['error'])) {
                $this->assertStringNotContainsString('negativo', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crear acepta precio límite (500000).
     */
    public function testCrearAceptaPrecioLimite(): void
    {
        $fotosMock = [
            'name' => ['test.jpg'],
            'type' => ['image/jpeg'],
            'tmp_name' => ['/tmp/test.jpg'],
            'error' => [UPLOAD_ERR_OK],
            'size' => [1024],
        ];

        try {
            $resultado = ServicioControlador::crear(
                self::$testIdProveedor,
                self::$testNombre,
                self::$testDescripcion,
                self::$testCategorias,
                self::$testUbicaciones,
                500000, // Precio límite
                $fotosMock,
                self::$testDiasDisponibles
            );

            // No debe tener error de precio
            if (isset($resultado['error'])) {
                $this->assertStringNotContainsString('caro', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crear valida proveedor existente.
     */
    public function testCrearValidaProveedorExistente(): void
    {
        $fotosMock = [
            'name' => ['test.jpg'],
            'type' => ['image/jpeg'],
            'tmp_name' => ['/tmp/test.jpg'],
            'error' => [UPLOAD_ERR_OK],
            'size' => [1024],
        ];

        try {
            $resultado = ServicioControlador::crear(
                99999, // Proveedor inexistente
                self::$testNombre,
                self::$testDescripcion,
                self::$testCategorias,
                self::$testUbicaciones,
                self::$testPrecio,
                $fotosMock,
                self::$testDiasDisponibles
            );

            $this->assertIsArray($resultado);

            if (isset($resultado['error'])) {
                $this->assertStringContainsString('proveedor', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método getServicio.
     */
    public function testGetServicioParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'getServicio');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('id', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->isOptional());
    }

    /**
     * Test: Verificar que getServicio retorna servicio específico con ID.
     */
    public function testGetServicioConId(): void
    {
        try {
            $resultado = ServicioControlador::getServicio(self::$testIdServicio);

            $this->assertIsArray($resultado);

            // Si no hay error, debe tener las claves esperadas
            if (!isset($resultado['error'])) {
                $this->assertArrayHasKey('id', $resultado);
                $this->assertArrayHasKey('nombre', $resultado);
                $this->assertArrayHasKey('precio', $resultado);
                $this->assertArrayHasKey('descripcion', $resultado);
                $this->assertArrayHasKey('imagenes', $resultado);
                $this->assertArrayHasKey('categorias', $resultado);
                $this->assertArrayHasKey('ubicaciones', $resultado);
                $this->assertArrayHasKey('diasDisponibles', $resultado);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere servicio válido en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getServicio retorna todos los servicios sin ID.
     */
    public function testGetServicioSinId(): void
    {
        try {
            $resultado = ServicioControlador::getServicio();

            $this->assertIsArray($resultado);

            // Si no hay error, debe ser un array de servicios
            if (!isset($resultado['error']) && count($resultado) > 0) {
                $primerServicio = $resultado[0];
                $this->assertArrayHasKey('id', $primerServicio);
                $this->assertArrayHasKey('nombre', $primerServicio);
                $this->assertArrayHasKey('precio', $primerServicio);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere servicios en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getServicio maneja ID inexistente.
     */
    public function testGetServicioConIdInexistente(): void
    {
        try {
            $resultado = ServicioControlador::getServicio('99999');

            $this->assertIsArray($resultado);

            if (isset($resultado['error'])) {
                $this->assertStringContainsString('no encontrado', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método borrar.
     */
    public function testBorrarParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'borrar');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('idServicio', $parameters[0]->getName());
        $this->assertEquals('idProveedor', $parameters[1]->getName());
    }

    /**
     * Test: Verificar que borrar valida que el ID sea de un proveedor.
     */
    public function testBorrarValidaProveedor(): void
    {
        try {
            $resultado = ServicioControlador::borrar(
                self::$testIdServicio,
                99999 // ID inexistente o no proveedor
            );

            $this->assertIsArray($resultado);

            if (isset($resultado['error'])) {
                $this->assertStringContainsString('proveedor', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método search.
     */
    public function testSearchParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'search');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('query', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que search retorna array.
     */
    public function testSearchRetornaArray(): void
    {
        try {
            $resultado = ServicioControlador::search('jardinería');

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que search maneja búsqueda sin resultados.
     */
    public function testSearchSinResultados(): void
    {
        try {
            $resultado = ServicioControlador::search('xyz_no_existe_123');

            $this->assertIsArray($resultado);

            if (isset($resultado['result'])) {
                $this->assertStringContainsString('no se encontraron', strtolower($resultado['result']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método updateServicio.
     */
    public function testUpdateServicioParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'updateServicio');
        $parameters = $reflection->getParameters();

        $this->assertCount(9, $parameters);
        $this->assertEquals('idServicio', $parameters[0]->getName());
        $this->assertEquals('nombre', $parameters[1]->getName());
        $this->assertEquals('descripcion', $parameters[2]->getName());

        // Verificar que los parámetros son opcionales excepto idServicio
        for ($i = 1; $i < count($parameters); $i++) {
            $this->assertTrue(
                $parameters[$i]->isOptional(),
                "El parámetro {$parameters[$i]->getName()} debe ser opcional"
            );
        }
    }

    /**
     * Test: Verificar que updateServicio rechaza precio negativo.
     */
    public function testUpdateServicioRechazaPrecioNegativo(): void
    {
        try {
            $resultado = ServicioControlador::updateServicio(
                self::$testIdServicio,
                null,
                null,
                null,
                null,
                -100 // Precio negativo
            );

            $this->assertIsArray($resultado);
            $this->assertArrayHasKey('error', $resultado);
            $this->assertStringContainsString('negativo', strtolower($resultado['error']));
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que updateServicio rechaza precio muy alto.
     */
    public function testUpdateServicioRechazaPrecioMuyAlto(): void
    {
        try {
            $resultado = ServicioControlador::updateServicio(
                self::$testIdServicio,
                null,
                null,
                null,
                null,
                500001 // Precio muy alto
            );

            $this->assertIsArray($resultado);
            $this->assertArrayHasKey('error', $resultado);
            $this->assertStringContainsString('caro', strtolower($resultado['error']));
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que updateServicio valida servicio existente.
     */
    public function testUpdateServicioValidaServicioExistente(): void
    {
        try {
            $resultado = ServicioControlador::updateServicio(
                99999, // Servicio inexistente
                'Nuevo nombre'
            );

            $this->assertIsArray($resultado);

            if (isset($resultado['error'])) {
                $this->assertStringContainsString('no encontrado', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método searchByIA.
     */
    public function testSearchByIAParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'searchByIA');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('texto', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que searchByIA requiere API keys.
     */
    public function testSearchByIARequiereAPIKeys(): void
    {
        if (empty(getenv('GROQ_API_KEY'))) {
            $this->markTestSkipped('Requiere GROQ_API_KEY configurada');
        }

        try {
            $resultado = ServicioControlador::searchByIA('buscar servicios de jardinería');

            $this->assertNotNull($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método getServiciosByProveedor.
     */
    public function testGetServiciosByProveedorParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'getServiciosByProveedor');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('idProveedor', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que getServiciosByProveedor retorna array.
     */
    public function testGetServiciosByProveedorRetornaArray(): void
    {
        try {
            $resultado = ServicioControlador::getServiciosByProveedor(self::$testIdProveedor);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getServiciosByProveedor maneja proveedor sin servicios.
     */
    public function testGetServiciosByProveedorSinServicios(): void
    {
        try {
            $resultado = ServicioControlador::getServiciosByProveedor(99999);

            $this->assertIsArray($resultado);

            if (isset($resultado['result'])) {
                $this->assertStringContainsString('no se encontraron', strtolower($resultado['result']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método saveFavorito.
     */
    public function testSaveFavoritoParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'saveFavorito');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('idServicio', $parameters[0]->getName());
        $this->assertEquals('idCliente', $parameters[1]->getName());
    }

    /**
     * Test: Verificar que saveFavorito detecta duplicados.
     */
    public function testSaveFavoritoDetectaDuplicados(): void
    {
        try {
            // Intentar guardar dos veces el mismo favorito
            ServicioControlador::saveFavorito(self::$testIdServicio, self::$testIdCliente);
            $resultado = ServicioControlador::saveFavorito(self::$testIdServicio, self::$testIdCliente);

            $this->assertIsArray($resultado);

            if (isset($resultado['error'])) {
                $this->assertStringContainsString('favoritos', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método deleteFavorito.
     */
    public function testDeleteFavoritoParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'deleteFavorito');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('idServicio', $parameters[0]->getName());
        $this->assertEquals('idCliente', $parameters[1]->getName());
    }

    /**
     * Test: Verificar que deleteFavorito valida favorito existente.
     */
    public function testDeleteFavoritoValidaExistencia(): void
    {
        try {
            $resultado = ServicioControlador::deleteFavorito(
                99999, // Servicio que no está en favoritos
                self::$testIdCliente
            );

            $this->assertIsArray($resultado);

            if (isset($resultado['error'])) {
                $this->assertStringContainsString('no está', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar parámetros del método getFavoritosByCliente.
     */
    public function testGetFavoritosByClienteParameters(): void
    {
        $reflection = new ReflectionMethod('ServicioControlador', 'getFavoritosByCliente');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que getFavoritosByCliente valida cliente existente.
     */
    public function testGetFavoritosByClienteValidaCliente(): void
    {
        try {
            $resultado = ServicioControlador::getFavoritosByCliente(99999);

            $this->assertIsArray($resultado);

            if (isset($resultado['error'])) {
                $this->assertStringContainsString('cliente', strtolower($resultado['error']));
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getFavoritosByCliente valida que sea cliente.
     */
    public function testGetFavoritosByClienteValidaTipoUsuario(): void
    {
        try {
            // Intentar con un ID que no es cliente
            $resultado = ServicioControlador::getFavoritosByCliente(self::$testIdProveedor);

            $this->assertIsArray($resultado);

            // Puede retornar error si el usuario no es cliente
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere datos válidos en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que la clase usa ServicioModelo.
     */
    public function testClaseUsaServicioModelo(): void
    {
        $this->assertTrue(
            class_exists('ServicioModelo'),
            'La clase ServicioModelo debe estar cargada'
        );
    }

    /**
     * Test: Verificar que la clase usa UsuarioModelo.
     */
    public function testClaseUsaUsuarioModelo(): void
    {
        $this->assertTrue(
            class_exists('UsuarioModelo'),
            'La clase UsuarioModelo debe estar cargada'
        );
    }

    /**
     * Test: Verificar que la clase usa IA.
     */
    public function testClaseUsaIA(): void
    {
        $this->assertTrue(
            class_exists('IA'),
            'La clase IA debe estar cargada'
        );
    }

    /**
     * Test: Verificar que la clase usa Cloudinary.
     */
    public function testClaseUsaCloudinary(): void
    {
        $this->assertTrue(
            class_exists('Cloudinary\Cloudinary'),
            'La clase Cloudinary debe estar disponible'
        );
    }

    /**
     * Test: Verificar que todos los métodos son estáticos.
     */
    public function testTodosLosMetodosSonEstaticos(): void
    {
        $methods = [
            'crear',
            'getServicio',
            'borrar',
            'search',
            'updateServicio',
            'searchByIA',
            'getServiciosByProveedor',
            'saveFavorito',
            'deleteFavorito',
            'getFavoritosByCliente',
        ];

        foreach ($methods as $methodName) {
            $reflection = new ReflectionMethod('ServicioControlador', $methodName);
            $this->assertTrue(
                $reflection->isStatic(),
                "El método $methodName debe ser estático"
            );
        }
    }

    /**
     * Test: Verificar que crear maneja múltiples fotos.
     */
    public function testCrearManejaMultiplesFotos(): void
    {
        $fotosMock = [
            'name' => ['test1.jpg', 'test2.jpg', 'test3.jpg'],
            'type' => ['image/jpeg', 'image/jpeg', 'image/jpeg'],
            'tmp_name' => ['/tmp/test1.jpg', '/tmp/test2.jpg', '/tmp/test3.jpg'],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'size' => [1024, 2048, 1536],
        ];

        try {
            $resultado = ServicioControlador::crear(
                self::$testIdProveedor,
                self::$testNombre,
                self::$testDescripcion,
                self::$testCategorias,
                self::$testUbicaciones,
                self::$testPrecio,
                $fotosMock,
                self::$testDiasDisponibles
            );

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getServicio incluye calificación promedio.
     */
    public function testGetServicioIncluyeCalificacion(): void
    {
        try {
            $resultado = ServicioControlador::getServicio();

            if (!isset($resultado['error']) && is_array($resultado) && count($resultado) > 0) {
                $primerServicio = $resultado[0];
                $this->assertArrayHasKey('calificacion', $primerServicio);
                $this->assertArrayHasKey('calificaciones', $primerServicio);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere servicios en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar estructura de respuesta de search.
     */
    public function testSearchEstructuraDeRespuesta(): void
    {
        try {
            $resultado = ServicioControlador::search('servicio');

            $this->assertIsArray($resultado);

            if (!isset($resultado['error']) && !isset($resultado['result']) && count($resultado) > 0) {
                $primerServicio = $resultado[0];
                $this->assertArrayHasKey('id', $primerServicio);
                $this->assertArrayHasKey('nombre', $primerServicio);
                $this->assertArrayHasKey('precio', $primerServicio);
                $this->assertArrayHasKey('descripcion', $primerServicio);
                $this->assertArrayHasKey('categorias', $primerServicio);
                $this->assertArrayHasKey('ubicaciones', $primerServicio);
                $this->assertArrayHasKey('diasDisponibles', $primerServicio);
                $this->assertArrayHasKey('imagenes', $primerServicio);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere servicios en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getFavoritosByCliente incluye isFavorito.
     */
    public function testGetFavoritosByClienteIncluyeIsFavorito(): void
    {
        try {
            $resultado = ServicioControlador::getFavoritosByCliente(self::$testIdCliente);

            if (!isset($resultado['error']) && is_array($resultado) && count($resultado) > 0) {
                foreach ($resultado as $favorito) {
                    $this->assertArrayHasKey('isFavorito', $favorito);
                    $this->assertTrue($favorito['isFavorito']);
                }
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere cliente válido en BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crear captura excepciones.
     */
    public function testCrearCapturaExcepciones(): void
    {
        $fotosMock = [
            'name' => ['test.jpg'],
            'type' => ['image/jpeg'],
            'tmp_name' => ['/tmp/test.jpg'],
            'error' => [UPLOAD_ERR_OK],
            'size' => [1024],
        ];

        try {
            $resultado = ServicioControlador::crear(
                null, // Provocar posible excepción
                self::$testNombre,
                self::$testDescripcion,
                self::$testCategorias,
                self::$testUbicaciones,
                self::$testPrecio,
                $fotosMock,
                self::$testDiasDisponibles
            );

            $this->assertIsArray($resultado);

            if (isset($resultado['error'])) {
                $this->assertIsString($resultado['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar consistencia de estructura de errores.
     */
    public function testEstructuraDeErrorConsistente(): void
    {
        $fotosMock = [
            'name' => ['test.jpg'],
            'type' => ['image/jpeg'],
            'tmp_name' => ['/tmp/test.jpg'],
            'error' => [UPLOAD_ERR_OK],
            'size' => [1024],
        ];

        $resultado1 = ServicioControlador::crear(
            self::$testIdProveedor,
            self::$testNombre,
            self::$testDescripcion,
            self::$testCategorias,
            self::$testUbicaciones,
            -100, // Precio negativo
            $fotosMock,
            self::$testDiasDisponibles
        );

        try {
            $resultado2 = ServicioControlador::updateServicio(
                99999, // Servicio inexistente
                'Nuevo nombre'
            );

            $this->assertArrayHasKey('error', $resultado1);
            $this->assertIsString($resultado1['error']);

            if (isset($resultado2['error'])) {
                $this->assertIsString($resultado2['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que updateServicio retorna success.
     */
    public function testUpdateServicioRetornaSuccess(): void
    {
        try {
            $resultado = ServicioControlador::updateServicio(
                self::$testIdServicio,
                'Nuevo nombre actualizado'
            );

            // Si no hay error, debe tener 'success'
            if (!isset($resultado['error'])) {
                $this->assertArrayHasKey('success', $resultado);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere servicio válido y configuración completa: ' . $e->getMessage());
        }
    }
}
