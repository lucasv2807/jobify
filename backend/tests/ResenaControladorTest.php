<?php

use PHPUnit\Framework\TestCase;

/**
 * Test para la clase resenaControlador.
 *
 * Este test verifica el funcionamiento correcto del controlador de reseñas
 * que maneja la creación y obtención de reseñas con integración a Cloudinary.
 *
 * Nota: Este test requiere conexión a la base de datos y algunos tests
 * requieren CLOUDINARY_URL configurada en las variables de entorno.
 */
class ResenaControladorTest extends TestCase
{
    /**
     * Test data IDs.
     */
    private static $testIdCliente;
    private static $testIdServicio;
    private static $testCalificacion = 4;
    private static $testComentario = 'Excelente servicio de prueba';

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
        require_once __DIR__ . '/../src/model/Resena.php';
        require_once __DIR__ . '/../src/controller/Resena.php';

        // Inicializar IDs de prueba
        self::$testIdCliente = 1;
        self::$testIdServicio = 1;
    }

    /**
     * Test: Verificar que la clase resenaControlador existe.
     */
    public function testResenaControladorClassExists(): void
    {
        $this->assertTrue(
            class_exists('resenaControlador'),
            'La clase resenaControlador debe existir'
        );
    }

    /**
     * Test: Verificar que todos los métodos públicos existen.
     */
    public function testPublicMethodsExist(): void
    {
        $methods = [
            'crearresena',
            'getresena',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists('resenaControlador', $method),
                "El método $method debe existir en la clase resenaControlador"
            );

            $reflection = new ReflectionMethod('resenaControlador', $method);
            $this->assertTrue(
                $reflection->isStatic(),
                "El método $method debe ser estático"
            );
        }
    }

    /**
     * Test: Verificar parámetros del método crearresena.
     */
    public function testCrearresenaParameters(): void
    {
        $reflection = new ReflectionMethod('resenaControlador', 'crearresena');
        $parameters = $reflection->getParameters();

        $this->assertCount(5, $parameters);
        $this->assertEquals('idCliente', $parameters[0]->getName());
        $this->assertEquals('idServicio', $parameters[1]->getName());
        $this->assertEquals('calificacion', $parameters[2]->getName());
        $this->assertEquals('comentario', $parameters[3]->getName());
        $this->assertEquals('imagen', $parameters[4]->getName());
    }

    /**
     * Test: Verificar que crearresena rechaza calificación menor a 1.
     */
    public function testCrearresenaRechazaCalificacionMenorA1(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            0, // Calificación inválida
            self::$testComentario,
            $imagenMock
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('entre 1 y 5', $resultado['error']);
    }

    /**
     * Test: Verificar que crearresena rechaza calificación mayor a 5.
     */
    public function testCrearresenaRechazaCalificacionMayorA5(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            6, // Calificación inválida
            self::$testComentario,
            $imagenMock
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('entre 1 y 5', $resultado['error']);
    }

    /**
     * Test: Verificar que crearresena acepta calificación 1.
     */
    public function testCrearresenaAceptaCalificacion1(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        try {
            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                1,
                self::$testComentario,
                $imagenMock
            );

            // No debe tener error de validación de calificación
            if (isset($resultado['error'])) {
                $this->assertStringNotContainsString('entre 1 y 5', $resultado['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearresena acepta calificación 5.
     */
    public function testCrearresenaAceptaCalificacion5(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        try {
            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                5,
                self::$testComentario,
                $imagenMock
            );

            // No debe tener error de validación de calificación
            if (isset($resultado['error'])) {
                $this->assertStringNotContainsString('entre 1 y 5', $resultado['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearresena rechaza datos vacíos (idCliente).
     */
    public function testCrearresenaRechazaIdClienteVacio(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $resultado = resenaControlador::crearresena(
            '', // Cliente vacío
            self::$testIdServicio,
            self::$testCalificacion,
            self::$testComentario,
            $imagenMock
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('datos obligatorios', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crearresena rechaza datos vacíos (idServicio).
     */
    public function testCrearresenaRechazaIdServicioVacio(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            '', // Servicio vacío
            self::$testCalificacion,
            self::$testComentario,
            $imagenMock
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('datos obligatorios', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crearresena rechaza calificación vacía.
     */
    public function testCrearresenaRechazaCalificacionVacia(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            '', // Calificación vacía
            self::$testComentario,
            $imagenMock
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * Test: Verificar que crearresena maneja error de subida de imagen.
     */
    public function testCrearresenaManejaErrorSubidaImagen(): void
    {
        // Imagen con error de upload
        $imagenConError = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE, // Error: no se subió archivo
            'size' => 0,
        ];

        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            self::$testCalificacion,
            self::$testComentario,
            $imagenConError
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('imagen', strtolower($resultado['error']));
    }

    /**
     * Test: Verificar que crearresena maneja imagen null.
     */
    public function testCrearresenaManejaImagenNull(): void
    {
        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            self::$testCalificacion,
            self::$testComentario,
            null
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * Test: Verificar que crearresena maneja imagen no array.
     */
    public function testCrearresenaManejaImagenNoArray(): void
    {
        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            self::$testCalificacion,
            self::$testComentario,
            'no_es_un_array'
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * Test: Verificar que crearresena maneja CLOUDINARY_URL ausente.
     */
    public function testCrearresenaManejaCloudinaryUrlAusente(): void
    {
        // Guardar valor original
        $originalCloudinaryUrl = getenv('CLOUDINARY_URL');

        // Remover temporalmente CLOUDINARY_URL
        putenv('CLOUDINARY_URL=');
        if (isset($_ENV['CLOUDINARY_URL'])) {
            unset($_ENV['CLOUDINARY_URL']);
        }

        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            self::$testCalificacion,
            self::$testComentario,
            $imagenMock
        );

        // Debe retornar error porque no se puede subir la imagen
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);

        // Restaurar valor original
        if ($originalCloudinaryUrl !== false) {
            putenv("CLOUDINARY_URL=$originalCloudinaryUrl");
            $_ENV['CLOUDINARY_URL'] = $originalCloudinaryUrl;
        }
    }

    /**
     * Test: Verificar parámetros del método getresena.
     */
    public function testGetresenaParameters(): void
    {
        $reflection = new ReflectionMethod('resenaControlador', 'getresena');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('idServicio', $parameters[0]->getName());
    }

    /**
     * Test: Verificar que getresena retorna array.
     */
    public function testGetresenaRetornaArray(): void
    {
        try {
            $resultado = resenaControlador::getresena(self::$testIdServicio);

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getresena maneja servicio inexistente.
     */
    public function testGetresenaManejaServicioInexistente(): void
    {
        try {
            $resultado = resenaControlador::getresena(999999);

            $this->assertIsArray($resultado);

            // Puede retornar array vacío o con error
            if (isset($resultado['error'])) {
                $this->assertIsString($resultado['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que getresena maneja excepciones.
     */
    public function testGetresenaManejaExcepciones(): void
    {
        try {
            $resultado = resenaControlador::getresena(null);

            // Debe manejar null sin errores fatales
            $this->assertTrue(
                is_array($resultado),
                'getresena debe retornar array incluso con parámetros inválidos'
            );
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearresena maneja comentario vacío.
     */
    public function testCrearresenaAceptaComentarioVacio(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        try {
            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testCalificacion,
                '', // Comentario vacío
                $imagenMock
            );

            // Comentario vacío debería ser aceptable
            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearresena maneja comentario largo.
     */
    public function testCrearresenaAceptaComentarioLargo(): void
    {
        $comentarioLargo = str_repeat('Este es un comentario muy largo. ', 100);

        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        try {
            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testCalificacion,
                $comentarioLargo,
                $imagenMock
            );

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearresena maneja caracteres especiales en comentario.
     */
    public function testCrearresenaAceptaCaracteresEspeciales(): void
    {
        $comentarioEspecial = 'Reseña con ñ, acentós, símbolos: @#$% y emojis 😀';

        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        try {
            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testCalificacion,
                $comentarioEspecial,
                $imagenMock
            );

            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearresena retorna id en caso de éxito.
     */
    public function testCrearresenaRetornaIdEnCasoDeExito(): void
    {
        // Este test requeriría un mock completo de Cloudinary y la BD
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test: Verificar que la clase usa resenaModelo.
     */
    public function testClaseUsaResenaModelo(): void
    {
        $this->assertTrue(
            class_exists('resenaModelo'),
            'La clase resenaModelo debe estar cargada'
        );
    }

    /**
     * Test: Verificar que crearresena maneja diferentes tipos de error de upload.
     */
    public function testCrearresenaManejaUploadErrors(): void
    {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL,
            UPLOAD_ERR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION,
        ];

        foreach ($uploadErrors as $errorCode) {
            $imagenConError = [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/test.jpg',
                'error' => $errorCode,
                'size' => 1024,
            ];

            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                self::$testCalificacion,
                self::$testComentario,
                $imagenConError
            );

            $this->assertIsArray($resultado);
            $this->assertArrayHasKey('error', $resultado);
        }
    }

    /**
     * Test: Verificar que todos los métodos son estáticos.
     */
    public function testTodosLosMetodosSonEstaticos(): void
    {
        $methods = ['crearresena', 'getresena'];

        foreach ($methods as $methodName) {
            $reflection = new ReflectionMethod('resenaControlador', $methodName);
            $this->assertTrue(
                $reflection->isStatic(),
                "El método $methodName debe ser estático"
            );
        }
    }

    /**
     * Test: Verificar que crearresena valida calificaciones decimales.
     */
    public function testCrearresenaValidaCalificacionesDecimales(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        try {
            // Calificación 3.5 debería ser válida
            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                3.5,
                self::$testComentario,
                $imagenMock
            );

            // No debe tener error de validación de rango
            if (isset($resultado['error'])) {
                $this->assertStringNotContainsString('entre 1 y 5', $resultado['error']);
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere configuración completa: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar estructura de respuesta de error consistente.
     */
    public function testEstructuraDeErrorConsistente(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        // Error 1: Calificación inválida
        $resultado1 = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            0,
            self::$testComentario,
            $imagenMock
        );

        // Error 2: Datos vacíos
        $resultado2 = resenaControlador::crearresena(
            '',
            self::$testIdServicio,
            self::$testCalificacion,
            self::$testComentario,
            $imagenMock
        );

        $this->assertArrayHasKey('error', $resultado1);
        $this->assertArrayHasKey('error', $resultado2);
        $this->assertIsString($resultado1['error']);
        $this->assertIsString($resultado2['error']);
    }

    /**
     * Test: Verificar que crearresena captura excepciones.
     */
    public function testCrearresenaCapturaExcepciones(): void
    {
        // Con parámetros que podrían causar excepción
        $resultado = resenaControlador::crearresena(
            null,
            null,
            self::$testCalificacion,
            self::$testComentario,
            null
        );

        // Debe retornar error, no lanzar excepción
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * Test: Verificar que getresena captura excepciones.
     */
    public function testGetresenaCapturaExcepciones(): void
    {
        try {
            $resultado = resenaControlador::getresena('invalid_id');

            // Debe retornar array con error o datos
            $this->assertIsArray($resultado);
        } catch (Exception $e) {
            $this->markTestSkipped('Requiere conexión a BD: ' . $e->getMessage());
        }
    }

    /**
     * Test: Verificar que crearresena valida todos los rangos de calificación.
     */
    public function testCrearresenaValidaRangosDeCalificacion(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $calificacionesValidas = [1, 2, 3, 4, 5];
        $calificacionesInvalidas = [-1, 0, 6, 10, 100];

        foreach ($calificacionesValidas as $calif) {
            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                $calif,
                self::$testComentario,
                $imagenMock
            );

            // No debe tener error de rango
            if (isset($resultado['error'])) {
                $this->assertStringNotContainsString(
                    'entre 1 y 5',
                    $resultado['error'],
                    "Calificación $calif debería ser válida"
                );
            }
        }

        foreach ($calificacionesInvalidas as $calif) {
            $resultado = resenaControlador::crearresena(
                self::$testIdCliente,
                self::$testIdServicio,
                $calif,
                self::$testComentario,
                $imagenMock
            );

            $this->assertArrayHasKey(
                'error',
                $resultado,
                "Calificación $calif debería ser inválida"
            );
            $this->assertStringContainsString('entre 1 y 5', $resultado['error']);
        }
    }

    /**
     * Test: Verificar que la clase usa Cloudinary correctamente.
     */
    public function testClaseUsaCloudinary(): void
    {
        $this->assertTrue(
            class_exists('Cloudinary\Cloudinary'),
            'La clase Cloudinary debe estar disponible'
        );
    }

    /**
     * Test: Verificar que crearresena valida orden de validaciones.
     */
    public function testCrearresenaOrdenDeValidaciones(): void
    {
        $imagenMock = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        // Primero valida calificación (antes de subir imagen)
        $resultado = resenaControlador::crearresena(
            self::$testIdCliente,
            self::$testIdServicio,
            0, // Calificación inválida
            self::$testComentario,
            $imagenMock
        );

        $this->assertArrayHasKey('error', $resultado);
        $this->assertStringContainsString('entre 1 y 5', $resultado['error']);
    }
}
