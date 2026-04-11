<?php

class BlackList
{
    /**
     * Lista de palabras prohibidas/indebidas
     * Incluye insultos, palabras ofensivas, contenido inapropiado en español.
     */
    private static $palabrasProhibidas = [
        // Insultos comunes
        'idiota', 'estupido', 'estúpido', 'estupida', 'estúpida', 'imbecil', 'imbécil', 'tonto', 'tonta',
        'pendejo', 'pendeja', 'boludo', 'boluda', 'pelotudo', 'pelotuda',
        'gilipollas', 'capullo', 'mamón', 'mamona', 'cabrón', 'cabrona',
        'hijo de puta', 'hijueputa', 'hdp', 'hijo puta', 'hijoeputa',
        'puto', 'puta', 'zorra', 'zorro', 'prostituta', 'ramera',
        'marica', 'maricón', 'maricon', 'trolo', 'puto gay',

        // Palabras vulgares y obscenas
        'mierda', 'cagada', 'cagar', 'cagon', 'cagón',
        'carajo', 'coño', 'cono', 'verga', 'pene', 'pija', 'pito',
        'concha', 'vagina', 'chocha', 'chucha', 'teta', 'tetas',
        'culo', 'ojete', 'ano', 'recto',
        'chingar', 'joder', 'follar', 'coger', 'chupar', 'mamar',
        'pene', 'pija', 'chota', 'poronga', 'huevos', 'bolas', 'testículos',

        // Discriminación racial/étnica
        'negro de mierda', 'sudaca', 'chino de mierda', 'indio',
        'gitano', 'panchito', 'gringo', 'yanqui',

        // Contenido sexual explícito
        'sexo', 'porno', 'pornografia', 'pornografía', 'xxx',
        'masturbación', 'masturbar', 'pajear', 'paja',
        'orgasmo', 'eyacular', 'penetrar', 'penetración',

        // Drogas
        'cocaina', 'cocaína', 'marihuana', 'porro', 'mota', 'faso',
        'heroina', 'heroína', 'crack', 'lsd', 'extasis', 'éxtasis',
        'drogadicto', 'drogarse', 'fumar porro',

        // Violencia extrema
        'matar', 'asesinar', 'violar', 'violación', 'violador',
        'suicidio', 'suicidar', 'matarse', 'morir',
        'torturar', 'tortura', 'golpear', 'pegar',

        // Estafas y fraudes
        'estafa', 'estafador', 'fraude', 'scam', 'timador', 'timar',
        'robar', 'ladrón', 'ladron', 'robo', 'hurto',

        // Spam y publicidad engañosa
        'gana dinero facil', 'hazte rico', 'gratis totalmente',
        'oferta limitada ya', 'clickea aqui', 'click aquí',

        // Otros términos inapropiados
        'basura', 'porquería', 'porqueria', 'maldito', 'maldita',
        'desgraciado', 'desgraciada', 'infeliz', 'miserable',
        'retrasado', 'retrasada', 'retardado', 'subnormal',
    ];

    /**
     * Verifica si un texto contiene palabras prohibidas.
     *
     * @param string|array $texto El texto a verificar (puede ser string o array de strings)
     *
     * @return array ['contiene' => bool, 'palabras' => array] Resultado de la verificación
     */
    public static function contienePalabrasProhibidas($texto)
    {
        if (empty($texto)) {
            return ['contiene' => false, 'palabras' => []];
        }

        // Si es un array, concatenar todos los elementos
        if (is_array($texto)) {
            $texto = implode(' ', $texto);
        }

        // Normalizar el texto: convertir a minúsculas y eliminar acentos
        $textoNormalizado = self::normalizarTexto($texto);

        $palabrasEncontradas = [];

        foreach (self::$palabrasProhibidas as $palabra) {
            $palabraNormalizada = self::normalizarTexto($palabra);

            // Buscar la palabra como palabra completa o como parte de una palabra
            // Usar delimitadores \b para palabras completas
            $patron = '/\b' . preg_quote($palabraNormalizada, '/') . '\b/i';

            if (preg_match($patron, $textoNormalizado)) {
                $palabrasEncontradas[] = $palabra;
            }
        }

        return [
            'contiene' => !empty($palabrasEncontradas),
            'palabras' => $palabrasEncontradas,
        ];
    }

    /**
     * Valida un texto y lanza un error si contiene palabras prohibidas.
     *
     * @param string|array $texto El texto a validar (puede ser string o array de strings)
     * @param string       $campo Nombre del campo (para el mensaje de error)
     *
     * @return bool True si el texto es válido
     *
     * @throws Exception Si el texto contiene palabras prohibidas
     */
    public static function validarTexto($texto, $campo = 'texto')
    {
        // Si es un array, validar cada elemento
        if (is_array($texto)) {
            foreach ($texto as $item) {
                self::validarTexto($item, $campo);
            }

            return true;
        }

        $resultado = self::contienePalabrasProhibidas($texto);

        if ($resultado['contiene']) {
            $palabras = implode(', ', array_slice($resultado['palabras'], 0, 3));
            throw new Exception("El $campo contiene palabras inapropiadas u ofensivas. Por favor, revisa tu contenido.");
        }

        return true;
    }

    /**
     * Censura las palabras prohibidas en un texto reemplazándolas con asteriscos.
     *
     * @param string|array $texto El texto a censurar (puede ser string o array de strings)
     *
     * @return string|array El texto censurado (mismo tipo que la entrada)
     */
    public static function censurarTexto($texto)
    {
        if (empty($texto)) {
            return $texto;
        }

        // Si es un array, censurar cada elemento
        if (is_array($texto)) {
            return array_map([self::class, 'censurarTexto'], $texto);
        }

        $textoNormalizado = self::normalizarTexto($texto);
        $textoCensurado = $texto;

        foreach (self::$palabrasProhibidas as $palabra) {
            $palabraNormalizada = self::normalizarTexto($palabra);
            $patron = '/\b' . preg_quote($palabraNormalizada, '/') . '\b/i';

            // Reemplazar con asteriscos manteniendo la longitud
            $reemplazo = str_repeat('*', mb_strlen($palabra));
            $textoCensurado = preg_replace($patron, $reemplazo, $textoCensurado);
        }

        return $textoCensurado;
    }

    /**
     * Normaliza un texto para comparación:
     * - Convierte a minúsculas
     * - Elimina acentos
     * - Elimina espacios múltiples
     *
     * @param string $texto Texto a normalizar
     *
     * @return string Texto normalizado
     */
    private static function normalizarTexto($texto)
    {
        // Convertir a minúsculas
        $texto = mb_strtolower($texto, 'UTF-8');

        // Eliminar acentos
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
            'ü' => 'u', 'Ü' => 'u',
        ]);

        // Normalizar espacios
        $texto = preg_replace('/\s+/', ' ', $texto);

        return trim($texto);
    }

    /**
     * Agrega una nueva palabra a la lista negra.
     *
     * @param string $palabra Palabra a agregar
     *
     * @return bool True si se agregó correctamente
     */
    public static function agregarPalabra($palabra)
    {
        $palabra = trim(mb_strtolower($palabra, 'UTF-8'));

        if (!in_array($palabra, self::$palabrasProhibidas)) {
            self::$palabrasProhibidas[] = $palabra;

            return true;
        }

        return false;
    }

    /**
     * Obtiene la lista completa de palabras prohibidas.
     *
     * @return array Lista de palabras prohibidas
     */
    public static function obtenerListaCompleta()
    {
        return self::$palabrasProhibidas;
    }

    /**
     * Obtiene el conteo total de palabras en la blacklist.
     *
     * @return int Número de palabras prohibidas
     */
    public static function contarPalabras()
    {
        return count(self::$palabrasProhibidas);
    }
}
