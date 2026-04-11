<?php

use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    private function runResponse(string $method, array $args = []): string
    {
        $argsCode = implode(', ', array_map(static fn ($arg) => var_export($arg, true), $args));
        $responsePath = var_export(__DIR__ . '/../src/utils/Response.php', true);

        $script = "require_once {$responsePath}; Response::{$method}({$argsCode});";
        $command = 'php -d display_errors=0 -r ' . escapeshellarg($script);

        $output = shell_exec($command);

        $this->assertNotNull($output, 'No se pudo ejecutar el subproceso para Response');

        return trim($output);
    }

    private function runResponseDecoded(string $method, array $args = []): array
    {
        $output = $this->runResponse($method, $args);
        $decoded = json_decode($output, true);

        $this->assertIsArray($decoded, 'La salida debe ser JSON valido');
        $this->assertSame(JSON_ERROR_NONE, json_last_error(), 'No debe haber errores de JSON');

        return $decoded;
    }

    public function testResponseClassExists(): void
    {
        require_once __DIR__ . '/../src/utils/Response.php';

        $this->assertTrue(class_exists('Response'));
    }

    public function testSuccessMethodExists(): void
    {
        require_once __DIR__ . '/../src/utils/Response.php';

        $this->assertTrue(method_exists('Response', 'success'));

        $reflection = new ReflectionMethod('Response', 'success');
        $this->assertTrue($reflection->isStatic());
    }

    public function testErrorMethodExists(): void
    {
        require_once __DIR__ . '/../src/utils/Response.php';

        $this->assertTrue(method_exists('Response', 'error'));

        $reflection = new ReflectionMethod('Response', 'error');
        $this->assertTrue($reflection->isStatic());
    }

    public function testNotFoundMethodExists(): void
    {
        require_once __DIR__ . '/../src/utils/Response.php';

        $this->assertTrue(method_exists('Response', 'notFound'));

        $reflection = new ReflectionMethod('Response', 'notFound');
        $this->assertTrue($reflection->isStatic());
    }

    public function testSuccessWithDefaultParameters(): void
    {
        $decoded = $this->runResponseDecoded('success');

        $this->assertSame([
            'success' => true,
            'message' => 'Operación exitosa',
            'data' => [],
            'code' => 200,
        ], $decoded);
    }

    public function testSuccessWithCustomData(): void
    {
        $data = ['id' => 1, 'nombre' => 'Juan'];
        $message = 'Usuario creado exitosamente';
        $code = 201;

        $decoded = $this->runResponseDecoded('success', [$data, $message, $code]);

        $this->assertSame(true, $decoded['success']);
        $this->assertSame($message, $decoded['message']);
        $this->assertSame($data, $decoded['data']);
        $this->assertSame($code, $decoded['code']);
    }

    public function testSuccessWithEmptyArray(): void
    {
        $decoded = $this->runResponseDecoded('success', [[], 'Operacion completada']);

        $this->assertSame([], $decoded['data']);
        $this->assertSame('Operacion completada', $decoded['message']);
    }

    public function testSuccessWithComplexData(): void
    {
        $data = [
            'usuarios' => [
                ['id' => 1, 'nombre' => 'Juan'],
                ['id' => 2, 'nombre' => 'Maria'],
            ],
            'total' => 2,
            'pagina' => 1,
        ];

        $decoded = $this->runResponseDecoded('success', [$data, 'Usuarios obtenidos']);

        $this->assertSame($data, $decoded['data']);
        $this->assertSame('Usuarios obtenidos', $decoded['message']);
    }

    public function testErrorWithDefaultParameters(): void
    {
        $decoded = $this->runResponseDecoded('error');

        $this->assertSame([
            'success' => false,
            'message' => 'Ocurrió un error',
            'code' => 400,
        ], $decoded);
    }

    public function testErrorWithCustomMessage(): void
    {
        $message = 'Usuario no encontrado';
        $code = 404;

        $decoded = $this->runResponseDecoded('error', [$message, $code]);

        $this->assertSame(false, $decoded['success']);
        $this->assertSame($message, $decoded['message']);
        $this->assertSame($code, $decoded['code']);
    }

    public function testErrorWithErrorDetail(): void
    {
        $message = 'Error de validacion';
        $code = 422;
        $errorDetail = 'El campo email es requerido';

        $decoded = $this->runResponseDecoded('error', [$message, $code, $errorDetail]);

        $this->assertSame(false, $decoded['success']);
        $this->assertSame($message, $decoded['message']);
        $this->assertSame($code, $decoded['code']);
        $this->assertSame($errorDetail, $decoded['error']);
    }

    public function testErrorWithoutErrorDetail(): void
    {
        $message = 'Acceso denegado';
        $code = 403;

        $decoded = $this->runResponseDecoded('error', [$message, $code, null]);

        $this->assertArrayNotHasKey('error', $decoded);
        $this->assertSame($message, $decoded['message']);
        $this->assertSame($code, $decoded['code']);
    }

    public function testNotFound(): void
    {
        $decoded = $this->runResponseDecoded('notFound');

        $this->assertSame([
            'success' => false,
            'message' => 'Ruta no encontrada',
            'code' => 404,
        ], $decoded);
    }

    public function testSuccessGeneratesValidJSON(): void
    {
        $decoded = $this->runResponseDecoded('success', [['test' => 'value'], 'Test message']);
        $this->assertSame('Test message', $decoded['message']);
    }

    public function testErrorGeneratesValidJSON(): void
    {
        $decoded = $this->runResponseDecoded('error', ['Error message', 500]);
        $this->assertSame('Error message', $decoded['message']);
    }

    public function testNotFoundGeneratesValidJSON(): void
    {
        $decoded = $this->runResponseDecoded('notFound');
        $this->assertSame(404, $decoded['code']);
    }

    public function testSuccessResponseStructure(): void
    {
        $decoded = $this->runResponseDecoded('success', [['key' => 'value'], 'Test']);

        $this->assertArrayHasKey('success', $decoded);
        $this->assertArrayHasKey('message', $decoded);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('code', $decoded);
    }

    public function testErrorResponseStructure(): void
    {
        $decoded = $this->runResponseDecoded('error', ['Error', 400, 'Details']);

        $this->assertArrayHasKey('success', $decoded);
        $this->assertArrayHasKey('message', $decoded);
        $this->assertArrayHasKey('code', $decoded);
        $this->assertArrayHasKey('error', $decoded);
    }

    public function testNotFoundResponseStructure(): void
    {
        $decoded = $this->runResponseDecoded('notFound');

        $this->assertArrayHasKey('success', $decoded);
        $this->assertArrayHasKey('message', $decoded);
        $this->assertArrayHasKey('code', $decoded);
        $this->assertSame('Ruta no encontrada', $decoded['message']);
    }

    public function testSuccessWithVariousHTTPCodes(): void
    {
        $codes = [200, 201, 202, 204];

        foreach ($codes as $code) {
            $decoded = $this->runResponseDecoded('success', [[], 'Test', $code]);
            $this->assertSame($code, $decoded['code']);
        }
    }

    public function testErrorWithVariousHTTPCodes(): void
    {
        $codes = [400, 401, 403, 404, 422, 500, 503];

        foreach ($codes as $code) {
            $decoded = $this->runResponseDecoded('error', ['Test error', $code]);
            $this->assertSame($code, $decoded['code']);
        }
    }

    public function testSuccessWithSpecialCharacters(): void
    {
        $data = [
            'mensaje' => 'Nono Jose esta aqui',
            'precio' => 'EUR100.50',
            'simbolos' => '< > & " \' /',
        ];

        $decoded = $this->runResponseDecoded('success', [$data, 'Test con caracteres']);

        $this->assertSame($data, $decoded['data']);
        $this->assertSame('Test con caracteres', $decoded['message']);
    }

    public function testErrorWithSpecialCharacters(): void
    {
        $message = 'Error: El usuario Jose <admin@example.com> no existe';
        $errorDetail = 'Busqueda con "comillas" y \'apostrofes\'';

        $decoded = $this->runResponseDecoded('error', [$message, 400, $errorDetail]);

        $this->assertSame($message, $decoded['message']);
        $this->assertSame($errorDetail, $decoded['error']);
    }
}
