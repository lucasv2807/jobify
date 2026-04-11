<?php

use PHPUnit\Framework\TestCase;

/**
 * Test para la clase BlackList.
 *
 * Este test verifica el funcionamiento correcto de la clase BlackList
 * que maneja el filtrado de palabras prohibidas u ofensivas.
 */
class BlackListTest extends TestCase
{
    /**
     * Setup que se ejecuta antes de cada test.
     */
    protected function setUp(): void
    {
        // Cargar la clase BlackList
        require_once __DIR__ . '/../src/utils/blackList.php';
    }

    /**
     * Test: Verificar que la clase BlackList existe.
     */
    public function testBlackListClassExists(): void
    {
        $this->assertTrue(
            class_exists('BlackList'),
            'La clase BlackList debe existir'
        );
    }

    /**
     * Test: Verificar que todos los métodos públicos existen.
     */
    public function testPublicMethodsExist(): void
    {
        $methods = [
            'contienePalabrasProhibidas',
            'validarTexto',
            'censurarTexto',
            'agregarPalabra',
            'obtenerListaCompleta',
            'contarPalabras',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists('BlackList', $method),
                "El método $method debe existir en la clase BlackList"
            );

            $reflection = new ReflectionMethod('BlackList', $method);
            $this->assertTrue(
                $reflection->isStatic(),
                "El método $method debe ser estático"
            );
        }
    }

    /**
     * Test: Verificar que contienePalabrasProhibidas detecta texto limpio.
     */
    public function testContienePalabrasProhibidasConTextoLimpio(): void
    {
        $texto = 'Este es un texto completamente limpio y apropiado';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('contiene', $resultado);
        $this->assertArrayHasKey('palabras', $resultado);
        $this->assertFalse($resultado['contiene']);
        $this->assertEmpty($resultado['palabras']);
    }

    /**
     * Test: Verificar que detecta palabras prohibidas simples.
     */
    public function testDetectaPalabrasProhibidasSimples(): void
    {
        $texto = 'Eres un idiota';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertTrue($resultado['contiene']);
        $this->assertNotEmpty($resultado['palabras']);
        $this->assertContains('idiota', $resultado['palabras']);
    }

    /**
     * Test: Verificar que detecta palabras con acentos.
     */
    public function testDetectaPalabrasConAcentos(): void
    {
        $texto = 'Este es un texto estúpido';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertTrue($resultado['contiene']);
        $this->assertNotEmpty($resultado['palabras']);
    }

    /**
     * Test: Verificar que detecta múltiples palabras prohibidas.
     */
    public function testDetectaMultiplesPalabrasProhibidas(): void
    {
        $texto = 'Eres un idiota y un tonto';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertTrue($resultado['contiene']);
        $this->assertGreaterThanOrEqual(2, count($resultado['palabras']));
    }

    /**
     * Test: Verificar que detecta palabras en mayúsculas.
     */
    public function testDetectaPalabrasEnMayusculas(): void
    {
        $texto = 'ERES UN IDIOTA';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertTrue($resultado['contiene']);
        $this->assertNotEmpty($resultado['palabras']);
    }

    /**
     * Test: Verificar que detecta palabras en texto mixto.
     */
    public function testDetectaPalabrasEnTextoMixto(): void
    {
        $texto = 'Este texto tiene una palabra Estúpido en el medio';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertTrue($resultado['contiene']);
    }

    /**
     * Test: Verificar que maneja texto vacío.
     */
    public function testManejaTextoVacio(): void
    {
        $resultado = BlackList::contienePalabrasProhibidas('');

        $this->assertFalse($resultado['contiene']);
        $this->assertEmpty($resultado['palabras']);
    }

    /**
     * Test: Verificar que maneja null.
     */
    public function testManejaNull(): void
    {
        $resultado = BlackList::contienePalabrasProhibidas(null);

        $this->assertFalse($resultado['contiene']);
        $this->assertEmpty($resultado['palabras']);
    }

    /**
     * Test: Verificar que maneja arrays de strings.
     */
    public function testManejaArrayDeStrings(): void
    {
        $textos = ['Este es texto limpio', 'Este también'];
        $resultado = BlackList::contienePalabrasProhibidas($textos);

        $this->assertFalse($resultado['contiene']);
    }

    /**
     * Test: Verificar que detecta palabras en arrays.
     */
    public function testDetectaPalabrasEnArrays(): void
    {
        $textos = ['Este es texto limpio', 'Este tiene idiota'];
        $resultado = BlackList::contienePalabrasProhibidas($textos);

        $this->assertTrue($resultado['contiene']);
        $this->assertNotEmpty($resultado['palabras']);
    }

    /**
     * Test: Verificar validarTexto con texto limpio.
     */
    public function testValidarTextoConTextoLimpio(): void
    {
        $texto = 'Este es un servicio de plomería profesional';

        $resultado = BlackList::validarTexto($texto, 'descripción');

        $this->assertTrue($resultado);
    }

    /**
     * Test: Verificar validarTexto lanza excepción con palabras prohibidas.
     */
    public function testValidarTextoLanzaExcepcionConPalabrasProhibidas(): void
    {
        $texto = 'Este servicio es una mierda';

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/palabras inapropiadas/');

        BlackList::validarTexto($texto, 'descripción');
    }

    /**
     * Test: Verificar validarTexto con array limpio.
     */
    public function testValidarTextoConArrayLimpio(): void
    {
        $textos = ['Título limpio', 'Descripción apropiada'];

        $resultado = BlackList::validarTexto($textos, 'campos');

        $this->assertTrue($resultado);
    }

    /**
     * Test: Verificar validarTexto con array que contiene palabra prohibida.
     */
    public function testValidarTextoConArrayConPalabraProhibida(): void
    {
        $textos = ['Título limpio', 'Descripción con idiota'];

        $this->expectException(Exception::class);

        BlackList::validarTexto($textos, 'campos');
    }

    /**
     * Test: Verificar censurarTexto con texto limpio.
     */
    public function testCensurarTextoConTextoLimpio(): void
    {
        $texto = 'Este es un texto completamente limpio';
        $resultado = BlackList::censurarTexto($texto);

        $this->assertEquals($texto, $resultado);
    }

    /**
     * Test: Verificar censurarTexto censura palabras prohibidas.
     */
    public function testCensurarTextoCensuraPalabras(): void
    {
        $texto = 'Eres un idiota';
        $resultado = BlackList::censurarTexto($texto);

        $this->assertStringNotContainsString('idiota', $resultado);
        $this->assertStringContainsString('*', $resultado);
    }

    /**
     * Test: Verificar censurarTexto mantiene la longitud.
     */
    public function testCensurarTextoMantieneFormato(): void
    {
        $texto = 'Este texto tiene idiota aquí';
        $resultado = BlackList::censurarTexto($texto);

        // Debe contener asteriscos
        $this->assertStringContainsString('*', $resultado);
        // No debe contener la palabra original
        $this->assertStringNotContainsString('idiota', $resultado);
        // Debe mantener el resto del texto
        $this->assertStringContainsString('Este texto tiene', $resultado);
    }

    /**
     * Test: Verificar censurarTexto con múltiples palabras.
     */
    public function testCensurarTextoConMultiplesPalabras(): void
    {
        $texto = 'Eres un idiota y un tonto';
        $resultado = BlackList::censurarTexto($texto);

        $this->assertStringNotContainsString('idiota', $resultado);
        $this->assertStringNotContainsString('tonto', $resultado);
        $this->assertStringContainsString('*', $resultado);
    }

    /**
     * Test: Verificar censurarTexto con array.
     */
    public function testCensurarTextoConArray(): void
    {
        $textos = ['Este es limpio', 'Este tiene idiota'];
        $resultado = BlackList::censurarTexto($textos);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals('Este es limpio', $resultado[0]);
        $this->assertStringContainsString('*', $resultado[1]);
    }

    /**
     * Test: Verificar censurarTexto con texto vacío.
     */
    public function testCensurarTextoConTextoVacio(): void
    {
        $resultado = BlackList::censurarTexto('');

        $this->assertEquals('', $resultado);
    }

    /**
     * Test: Verificar censurarTexto con null.
     */
    public function testCensurarTextoConNull(): void
    {
        $resultado = BlackList::censurarTexto(null);

        $this->assertNull($resultado);
    }

    /**
     * Test: Verificar agregarPalabra.
     */
    public function testAgregarPalabra(): void
    {
        $palabraAntes = BlackList::contarPalabras();

        $nuevaPalabra = 'testpalabra123';
        $resultado = BlackList::agregarPalabra($nuevaPalabra);

        $this->assertTrue($resultado);
        $this->assertEquals($palabraAntes + 1, BlackList::contarPalabras());

        // Verificar que ahora detecta la palabra
        $texto = 'Este texto tiene testpalabra123';
        $deteccion = BlackList::contienePalabrasProhibidas($texto);
        $this->assertTrue($deteccion['contiene']);
    }

    /**
     * Test: Verificar agregarPalabra no agrega duplicados.
     */
    public function testAgregarPalabraNoDuplicados(): void
    {
        $palabraAntes = BlackList::contarPalabras();

        // Agregar palabra que ya existe
        $resultado = BlackList::agregarPalabra('idiota');

        $this->assertFalse($resultado);
        $this->assertEquals($palabraAntes, BlackList::contarPalabras());
    }

    /**
     * Test: Verificar agregarPalabra normaliza el texto.
     */
    public function testAgregarPalabraNormalizaTexto(): void
    {
        $nuevaPalabra = '  PALABRA123  ';
        BlackList::agregarPalabra($nuevaPalabra);

        // Debe detectar la palabra en minúsculas
        $texto = 'Este tiene palabra123';
        $resultado = BlackList::contienePalabrasProhibidas($texto);
        $this->assertTrue($resultado['contiene']);
    }

    /**
     * Test: Verificar obtenerListaCompleta.
     */
    public function testObtenerListaCompleta(): void
    {
        $lista = BlackList::obtenerListaCompleta();

        $this->assertIsArray($lista);
        $this->assertNotEmpty($lista);
        $this->assertContains('idiota', $lista);
    }

    /**
     * Test: Verificar contarPalabras.
     */
    public function testContarPalabras(): void
    {
        $conteo = BlackList::contarPalabras();

        $this->assertIsInt($conteo);
        $this->assertGreaterThan(0, $conteo);
    }

    /**
     * Test: Verificar contarPalabras coincide con obtenerListaCompleta.
     */
    public function testContarPalabrasCoincideConLista(): void
    {
        $conteo = BlackList::contarPalabras();
        $lista = BlackList::obtenerListaCompleta();

        $this->assertEquals(count($lista), $conteo);
    }

    /**
     * Test: Verificar detección de palabras con espacios.
     */
    public function testDetectaPalabrasConEspacios(): void
    {
        $texto = 'Eres un hijo de puta';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertTrue($resultado['contiene']);
    }

    /**
     * Test: Verificar que no detecta palabras como parte de otras.
     */
    public function testNoDetectaPalabrasParciales(): void
    {
        // "tonto" es palabra prohibida pero no debe detectarse en "montón"
        $texto = 'Hay un montón de trabajo';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        // Debe ser false o no contener "tonto"
        if ($resultado['contiene']) {
            $this->assertNotContains('tonto', $resultado['palabras']);
        }
    }

    /**
     * Test: Verificar normalización de acentos.
     */
    public function testNormalizacionAcentos(): void
    {
        // Detectar "estúpido" cuando se escribe "estupido"
        $texto = 'Eso es estupido';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertTrue($resultado['contiene']);
    }

    /**
     * Test: Verificar que maneja texto con saltos de línea.
     */
    public function testManejaTextoConSaltosDeLinea(): void
    {
        $texto = "Este es un texto\ncon saltos de línea\ny una palabra idiota";
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertTrue($resultado['contiene']);
    }

    /**
     * Test: Verificar que maneja texto con caracteres especiales.
     */
    public function testManejaTextoConCaracteresEspeciales(): void
    {
        $texto = 'Este texto tiene símbolos @#$% y es limpio';
        $resultado = BlackList::contienePalabrasProhibidas($texto);

        $this->assertFalse($resultado['contiene']);
    }

    /**
     * Test: Verificar censurado mantiene el formato.
     */
    public function testCensuradoMantieneLongitud(): void
    {
        $texto = 'palabra idiota palabra';
        $resultado = BlackList::censurarTexto($texto);

        // La longitud total debe ser similar (puede variar por los asteriscos)
        $this->assertGreaterThanOrEqual(
            strlen('palabra ****** palabra'),
            strlen($resultado)
        );
    }

    /**
     * Test: Verificar rendimiento con texto largo.
     */
    public function testRendimientoConTextoLargo(): void
    {
        $textoLargo = str_repeat('Este es un texto muy largo sin palabras prohibidas. ', 100);

        $inicio = microtime(true);
        $resultado = BlackList::contienePalabrasProhibidas($textoLargo);
        $tiempo = microtime(true) - $inicio;

        $this->assertFalse($resultado['contiene']);
        $this->assertLessThan(1, $tiempo, 'La verificación debe completarse en menos de 1 segundo');
    }

    /**
     * Test: Verificar que validarTexto incluye nombre del campo en error.
     */
    public function testValidarTextoIncluyeNombreCampo(): void
    {
        $texto = 'Este texto tiene idiota';

        try {
            BlackList::validarTexto($texto, 'título del servicio');
            $this->fail('Debería haber lanzado una excepción');
        } catch (Exception $e) {
            $this->assertStringContainsString('título del servicio', $e->getMessage());
        }
    }

    /**
     * Test: Verificar detección case-insensitive.
     */
    public function testDeteccionCaseInsensitive(): void
    {
        $variaciones = [
            'IDIOTA',
            'Idiota',
            'iDiOtA',
            'idiota',
        ];

        foreach ($variaciones as $variacion) {
            $resultado = BlackList::contienePalabrasProhibidas("Eres un $variacion");
            $this->assertTrue(
                $resultado['contiene'],
                "Debe detectar '$variacion'"
            );
        }
    }

    /**
     * Test: Verificar que la lista no está vacía.
     */
    public function testListaNoEstaVacia(): void
    {
        $lista = BlackList::obtenerListaCompleta();

        $this->assertNotEmpty($lista);
        $this->assertGreaterThan(50, count($lista), 'La lista debe tener al menos 50 palabras');
    }
}
