<?php

class Response
{
    /**
     * funcion para manejar respuestas exitosas en las respuestas de la API.
     *
     * Esta función se encarga de enviar los datos y toda la logica de la respuesta.
     *
     * @param array  $data    datos a enviar en la respuesta
     * @param string $message mensaje a mostrar al de respuesta
     * @param int    $code    código de respuesta HTTP
     *
     * @return never
     **/
    public static function success(array $data = [], string $message = 'Operación exitosa', int $code = 200)
    {
        http_response_code($code);

        echo json_encode([
          'success' => true,
          'message' => $message,
          'data' => $data,
          'code' => $code,
        ]);
        exit;
    }

    /**
     * funcion para manejar errores en las respuestas de la API.
     *
     * Esta función se encarga de enviar una respuesta de error en formato JSON.
     *
     * @param string      $message mensaje a mostrar al de respuesta
     * @param int         $code    código de respuesta HTTP
     * @param string|null $error   mensaje de error detallado (opcional)
     *
     * @return never
     **/
    public static function error(string $message = 'Ocurrió un error', int $code = 400, string | null $error = null)
    {
        http_response_code($code);

        $response = [
          'success' => false,
          'message' => $message,
          'code' => $code,
        ];

        if ($error !== null) {
            $response['error'] = $error;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * funcion para manejar errores de rutas en la API.
     *
     * Esta función se encarga de enviar una respuesta de error en formato JSON.
     *
     * @return never
     **/
    public static function notFound()
    {
        http_response_code(404);

        $response = [
          'success' => false,
          'message' => 'Ruta no encontrada',
          'code' => 404,
        ];

        echo json_encode($response);
        exit;
    }
}
