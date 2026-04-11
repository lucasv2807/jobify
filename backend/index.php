<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
// poner el tamaño de la subida a maximo 20MB
ini_set('upload_max_filesize', '20M');
// Ocultar errores en producción, solo registrarlos
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
// poner el tipo a application/json
// NOTA: Las cabeceras CORS se manejan en nginx.conf para evitar duplicados
header('Content-Type: application/json');
ini_set('expose_php', 'Off');

$request = $_SERVER['REQUEST_URI'];
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
$request = parse_url($request, PHP_URL_PATH);
$Ruta = explode('/', $request);
// !NO ELIMINAR, SIN ESTO PERDEMOS EL PROYECTO! !POR FAVOR!
if (empty($Ruta[1])) {
    echo json_encode(['status' => 'API funcionando']);
    exit;
}
// Un switch para manejar las rutas
// Rutas de la API
switch ($Ruta[1]) {
    // si la ruta es por ej:jobify/usuario se ejecuta el case 'usuario'
    case 'usuario':
        require_once 'src/routes/Usuario.php';
        break;
    case 'servicios':
        require_once 'src/routes/Servicio.php';
        break;
        // si la ruta no existe te manda a error 404
    case 'chat':
        require_once 'src/routes/Chat.php';
        break;
    case 'review':
        require_once 'src/routes/Resena.php';
        break;
    case 'reserva':
        require_once 'src/routes/Reserva.php';
        break;
    case 'pago':
        require_once 'src/routes/Pago.php';
        break;
    default:
        require_once 'src/utils/Response.php';
        Response::notFound();
        break;
}
