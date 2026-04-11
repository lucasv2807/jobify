<?php

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);
$Ruta = explode('/', $request);
$method = $_SERVER['REQUEST_METHOD'];
require_once 'src/controller/Usuario.php';
require_once 'src/utils/Response.php';
require_once 'src/utils/blackList.php';
switch ($method) {
    case 'POST':
        // Verificar si la ruta es /usuario/register
        if (isset($Ruta[2]) && $Ruta[2] == 'register') {
            // Crear una instancia del controlador Usuario
            $register = new UsuarioControlador();
            $nombre = $_POST['nombre'] ?? null;
            $apellido = $_POST['apellido'] ?? null;
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            // Los ?? '' quiere decir que si no existe la variable, se le asigna un valor por defecto que en este caso es una cadena vacía
            $telefono = $_POST['telefono'] ?? null;
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
            $foto_perfil = $_FILES['foto_perfil'] ?? null;
            $esProveedor = $_POST['es_proveedor'] ?? false;
            $nombreEmpresa = $_POST['nombre_empresa'] ?? null;
            $descripcion = $_POST['descripcion'] ?? null;
            $habilidades = $_POST['habilidades'] ?? null;
            $experiencia = $_POST['experiencia'] ?? null;
            $titulos = $_POST['titulos'] ?? null;
            if (!isset($nombre, $apellido, $email, $password, $fecha_nacimiento)) {
                Response::error('Faltan datos obligatorios', 400);
            }
            // Validar que nombre y apellido no contengan números
            if (preg_match('/\d/', $nombre)) {
                Response::error('El nombre no puede contener números', 400);
            }
            if (preg_match('/\d/', $apellido)) {
                Response::error('El apellido no puede contener números', 400);
            }
            // Validar palabras prohibidas en nombre y apellido
            try {
                BlackList::validarTexto($nombre, 'nombre');
                BlackList::validarTexto($apellido, 'apellido');
            } catch (Exception $e) {
                Response::error($e->getMessage(), 400);
            }
            if ($esProveedor == 'true' || $esProveedor === true) {
                if (!isset($nombreEmpresa, $descripcion, $habilidades, $experiencia)) {
                    Response::error('Faltan datos obligatorios para el registro como proveedor', 400);
                }
                // Validar palabras prohibidas en datos del proveedor
                try {
                    BlackList::validarTexto($nombreEmpresa, 'nombre de empresa');
                    BlackList::validarTexto($descripcion, 'descripción');
                    BlackList::validarTexto($experiencia, 'experiencia');
                } catch (Exception $e) {
                    Response::error($e->getMessage(), 400);
                }
            }
            // Llamar al método register del controlador Usuario
            $response = $register->register($nombre, $apellido, $email, $password, $fecha_nacimiento, $telefono, $foto_perfil, $esProveedor, $nombreEmpresa, $descripcion, $habilidades, $experiencia, $titulos);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Usuario registrado exitosamente', 201);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'updateProfile') {
            $idUsuario = $_POST['idUsuario'] ?? null;
            $nombre = $_POST['nombre'] ?? null;
            $apellido = $_POST['apellido'] ?? null;
            $email = $_POST['email'] ?? null;
            $telefono = $_POST['telefono'] ?? null;
            $foto_perfil = $_FILES['foto_perfil'] ?? null;
            $esProveedor = $_POST['es_proveedor'] ?? false;
            $nombreEmpresa = $_POST['nombre_empresa'] ?? null;
            $descripcion = $_POST['descripcion'] ?? null;
            $habilidades = $_POST['habilidades'] ?? null;
            $experiencia = $_POST['experiencia'] ?? null;
            $titulos = $_POST['titulos'] ?? null;

            // Log para debug
            error_log('UpdateProfile - Datos recibidos:');
            error_log("idUsuario: $idUsuario, esProveedor: $esProveedor");
            error_log("nombreEmpresa: $nombreEmpresa, descripcion: $descripcion, experiencia: $experiencia");
            error_log('habilidades: ' . json_encode($habilidades));
            error_log('titulos: ' . json_encode($titulos));

            // Solo validar campos obligatorios mínimos
            if (!isset($idUsuario)) {
                Response::error('Falta el ID de usuario', 400);
            }
            // Validar que nombre y apellido no contengan números (si se están actualizando)
            if (isset($nombre) && !empty($nombre) && preg_match('/\d/', $nombre)) {
                Response::error('El nombre no puede contener números', 400);
            }
            if (isset($apellido) && !empty($apellido) && preg_match('/\d/', $apellido)) {
                Response::error('El apellido no puede contener números', 400);
            }
            // Validar palabras prohibidas (solo en campos que se están actualizando)
            try {
                if (isset($nombre) && !empty($nombre)) {
                    BlackList::validarTexto($nombre, 'nombre');
                }
                if (isset($apellido) && !empty($apellido)) {
                    BlackList::validarTexto($apellido, 'apellido');
                }
            } catch (Exception $e) {
                Response::error($e->getMessage(), 400);
            }
            if ($esProveedor == 'true' || $esProveedor === true) {
                // Si es proveedor, al menos necesita nombre empresa y descripción
                if (empty($nombreEmpresa) && empty($descripcion)) {
                    Response::error('Para actualizar un proveedor, debe proporcionar al menos nombre de empresa o descripción', 400);
                }
                // Validar palabras prohibidas en datos del proveedor
                try {
                    if (isset($nombreEmpresa) && !empty($nombreEmpresa)) {
                        BlackList::validarTexto($nombreEmpresa, 'nombre de empresa');
                    }
                    if (isset($descripcion) && !empty($descripcion)) {
                        BlackList::validarTexto($descripcion, 'descripción');
                    }
                    if (isset($experiencia) && !empty($experiencia)) {
                        BlackList::validarTexto($experiencia, 'experiencia');
                    }
                } catch (Exception $e) {
                    Response::error($e->getMessage(), 400);
                }
            }
            $updateProfile = new UsuarioControlador();
            $response = $updateProfile->updateProfile($idUsuario, $nombre, $apellido, $email, $telefono, $foto_perfil, $esProveedor, $nombreEmpresa, $descripcion, $habilidades, $experiencia, $titulos);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Perfil actualizado exitosamente', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'updatePassword') {
            $updatePassword = new UsuarioControlador();
            $idUsuario = $_POST['idUsuario'] ?? null;
            $passwordActual = $_POST['passwordActual'] ?? null;
            $nuevoPassword = $_POST['nuevoPassword'] ?? null;
            if (!isset($idUsuario, $passwordActual, $nuevoPassword)) {
                Response::error('Faltan datos', 400);
            }
            $response = $updatePassword->updatePassword($idUsuario, $passwordActual, $nuevoPassword);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Contraseña actualizada exitosamente', 200);
        } else {
            Response::notFound();
        }
        break;
    case 'GET':
        // Verificar si la ruta es /usuario/login
        if (isset($Ruta[2]) && $Ruta[2] == 'login') {
            $login = new UsuarioControlador();
            $email = $_GET['email'];
            $password = $_GET['password'];
            if (!isset($email, $password)) {
                Response::error('Faltan datos', 400);
            }
            // Llamar al método login del controlador Usuario
            $data = $login->login($email, $password);
            if (isset($data['error'])) {
                Response::error($data['error'], 500);
            }
            Response::success($data, 'Login exitoso', 200);
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'getUser') {
            $getUser = new UsuarioControlador();
            $id = $_GET['id'] ?? null;
            $response = $getUser->getUser($id);
            if (isset($response['error'])) {
                Response::error($response['error'], 500);
            }
            Response::success($response, 'Usuario encontrado', 200);
        } else {
            // Si la ruta no es /usuario/getUser, retornar un error 404
            Response::notFound();
        }
        break;
    default:
        Response::error('Método no permitido', 405);
        break;
}
