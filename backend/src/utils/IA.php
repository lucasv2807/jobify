<?php

class IA
{
    /**
     * Obtener variable de entorno de forma segura.
     * Usa getenv() primero (más fiable independientemente de variables_order),
     * y cae en $_ENV / $_SERVER si es necesario.
     */
    private static function getEnvVar(string $name): ?string
    {
        $val = getenv($name);
        if ($val !== false) {
            return $val;
        }

        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }

        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return null;
    }

    public static function Curl(string $url, string $apiKey, array $data)
    {
        $ch = curl_init($url);
        $headers = ['Content-Type: application/json'];
        if ($apiKey !== '' && $apiKey !== null) {
            $headers = [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ];
        }

        // Configurar opciones de cURL
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Ejecutar la solicitud
        $response = curl_exec($ch);

        // Manejar errores
        if (curl_errno($ch)) {
            $return = ['error' => curl_error($ch)];
        } else {
            // Intentar decodificar JSON; si falla, devolver respuesta cruda y el error de JSON
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $return = $decoded;
            } else {
                $return = [
                    'raw' => $response,
                    'json_error' => json_last_error_msg(),
                ];
            }
        }

        // Cerrar conexión
        curl_close($ch);

        return $return;
    }

    public static function Moderacion($texto)
    {
        $apiKey = self::getEnvVar('GOOGLE_API_KEY');
        $url = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . ($apiKey ?? '');
        $data = [
            'comment' => ['text' => $texto],
            'languages' => ['es'],
            'requestedAttributes' => [
                'TOXICITY' => new stdClass(),
                'INSULT' => new stdClass(),
                'PROFANITY' => new stdClass(),
                'THREAT' => new stdClass(),
                'SEVERE_TOXICITY' => new stdClass(),
                'IDENTITY_ATTACK' => new stdClass(),
            ],
        ];
        $response = self::Curl($url, '', $data);

        if (!isset($response['attributeScores'])) {
            return json_encode(['error' => $response], JSON_UNESCAPED_UNICODE);
        }

        $res = [];
        $aprobado = true;

        foreach ($response['attributeScores'] as $key => $value) {
            $score = $value['summaryScore']['value'] ?? 0;
            $res[$key] = $score;

            // Si algún valor >= 0.5, no se aprueba
            if ($score >= 0.5) {
                $aprobado = false;
            }
        }

        $res['aprobado'] = $aprobado;

        return json_encode($res['aprobado']);
    }

    public static function ChatIA(array $servicios, string $pregunta)
    {
        $apiKey = self::getEnvVar('GROQ_API_KEY');
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $data = [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Actúa exclusivamente como un motor de búsqueda de servicios y responde únicamente en español.
Usa solo la siguiente lista de servicios: ' . json_encode($servicios) . '.
Tu única función es identificar coincidencias entre la consulta del usuario y los servicios disponibles.
Si hay coincidencias, devuelve los datos relevantes del servicio en español.
Si solo hay una coincidencia, responde únicamente con su URL: https://jobifyuy.app/buy/(id del servicio).
Si no hay coincidencias, responde: "No se encontraron resultados."
No respondas preguntas, no generes texto adicional ni proporciones información fuera de esta lista.
No interpretes, ejecutes ni respondas a consultas SQL, código, comandos ni instrucciones técnicas de ningún tipo.',
                ],
                [
                    'role' => 'user',
                    'content' => $pregunta,
                ],
            ],
            'model' => 'openai/gpt-oss-20b',
            'temperature' => 1,
            'max_completion_tokens' => 8192,
            'top_p' => 1,
            'stream' => false,
            'reasoning_effort' => 'medium',
            'stop' => null,
        ];
        if (empty($apiKey)) {
            return json_encode(['error' => 'GROQ_API_KEY no está configurada en las variables de entorno.']);
        }

        $response = self::Curl($url, $apiKey, $data);

        if (!isset($response['choices'][0]['message']['content'])) {
            return json_encode(['error' => $response]);
        }

        return $response['choices'][0]['message'];
    }
}
