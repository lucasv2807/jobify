<?php

require_once 'src/model/Usuario.php';
require_once 'src/utils/IA.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\FocusOn;
use Cloudinary\Transformation\Format;
use Cloudinary\Transformation\Quality;

class UsuarioControlador
{
    // Metodo para registrar un nuevo usuario
    public static function register($nombre, $apellido, $email, $password, $fecha_nacimiento, $telefono, $foto_perfil, $esProveedor, $nombreEmpresa, $descripcion, $habilidades, $experiencia, $titulos)
    {
        try {
            // Validar edad mínima de 18 años
            $fechaNac = new DateTime($fecha_nacimiento);
            $hoy = new DateTime();
            $edad = $hoy->diff($fechaNac)->y;

            if ($edad < 18) {
                return ['error' => 'Debes tener al menos 18 años para registrarte'];
            }

            if ($edad > 100) {
                return ['error' => 'La edad no puede ser mayor a 100 años'];
            }

            // Si es proveedor, validar coherencia entre edad y experiencia
            if (isset($esProveedor) && ($esProveedor === 'true' || $esProveedor === true || $esProveedor === 1 || $esProveedor === '1')) {
                if ($experiencia !== null && is_numeric($experiencia)) {
                    $experienciaAnios = intval($experiencia);
                    // La experiencia no puede ser mayor a edad - 18 (asumiendo que empezó a trabajar a los 18)
                    $maxExperiencia = $edad - 18;
                    if ($experienciaAnios > $maxExperiencia) {
                        return ['error' => 'La experiencia laboral (' . $experienciaAnios . ' años) no es coherente con tu edad (' . $edad . ' años). La experiencia máxima posible sería ' . $maxExperiencia . ' años'];
                    }
                    if ($experienciaAnios < 0) {
                        return ['error' => 'La experiencia laboral debe ser un número positivo'];
                    }
                }
            }

            if (isset($Moderacion['error'])) {
                return ['error' => 'Error en la moderación de contenido'];
            }
            if ($Moderacion === "false") {
                return ['error' => 'El contenido proporcionado no cumple con las políticas de uso.'];
            }
            $UsuarioModelo = new UsuarioModelo();
            $fotoPerfil = self::uploadFoto($foto_perfil, $nombre);
            // Se encripta la contraseña
            $password = password_hash($password, PASSWORD_DEFAULT);
            // Detecta si estas creando un usuario proveedor o un usuario normal
            if (isset($esProveedor) && ($esProveedor === 'true' || $esProveedor === true || $esProveedor === 1 || $esProveedor === '1')) {
                $ModeracionProveedor = IA::Moderacion('Nombre Empresa: ' . $nombreEmpresa . ', Descripción: ' . $descripcion . ', Habilidades: ' . implode(', ', $habilidades) . ', Títulos: ' . implode(', ', $titulos));
                 if (isset($ModeracionProveedor['error'])) {
                     return ['error' => 'Error en la moderación de contenido del proveedor'];
                 }
                 if ($ModeracionProveedor === "false") {
                     return ['error' => 'El contenido proporcionado para el proveedor no cumple con las políticas de uso.'];
                 }
                $id = $UsuarioModelo->crearUsuarioProveedor($nombre, $apellido, $email, $password, $fecha_nacimiento, $fotoPerfil, $telefono, $nombreEmpresa, $descripcion, $experiencia, $habilidades, $titulos);
            } else {
                $id = $UsuarioModelo->crearUsuario($nombre, $apellido, $email, $password, $fecha_nacimiento, $fotoPerfil, $telefono);
                $UsuarioModelo->crearCliente($id);
            }
            // Si el ID es devuelto, significa que el usuario fue creado exitosamente
            if ($id) {
                return ['id' => $id];
            } else {
                return ['error' => 'No se pudo crear el usuario'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Metodo para subir una foto de perfil
    private static function uploadFoto($foto_perfil, $nombre)
    {
        // Imagen fallback para cuando no hay archivo o Cloudinary no esta disponible.
        $fallbackFoto = 'https://api.dicebear.com/9.x/initials/svg?seed=' . $nombre . '&backgroundColor=00acc1,039be5,1e88e5,3949ab,5e35b1,8e24aa,d81b60,e53935,f4511e,fb8c00,fdd835,ffb300&backgroundType=gradientLinear&fontFamily=Arial&fontWeight=700';

        // Si no se envio foto, no intentes inicializar Cloudinary.
        if (!is_array($foto_perfil) || $foto_perfil['error'] !== UPLOAD_ERR_OK) {
            return $fallbackFoto;
        }

        $cloudinaryUrl = $_ENV['CLOUDINARY_URL'] ?? getenv('CLOUDINARY_URL');
        if ($cloudinaryUrl === false || empty($cloudinaryUrl)) {
            error_log('uploadFoto: CLOUDINARY_URL not set');
            return $fallbackFoto;
        }

        // Se crea una instancia de Cloudinary con su configuracion
        $config = Configuration::fromCloudinaryUrl($cloudinaryUrl);
        $cloudinary = new Cloudinary($config);
        // Se detecta si la foto de perfil existe
        try {
            // Se sube la foto a cloudinary con un nombre temporal
            $upload = $cloudinary->uploadApi()->upload($foto_perfil['tmp_name'], [
                'folder' => 'jobify/usuarios',
                'public_id' => uniqid('perfil_'),
            ]);
            // Se guarda el id de la imagen y luego se transforma al formato mas adecuado
            $publicId = $upload['public_id'];
            $fotoUrl = $cloudinary->image($publicId)
                ->resize(Resize::thumbnail()->width(150)->height(150)->gravity(Gravity::focusOn(FocusOn::face())))
                ->quality(Quality::auto())
                ->format(Format::auto())
                ->toUrl();
        } catch (Exception $e) {
            error_log('Error al subir imagen a Cloudinary: ' . $e->getMessage());
            $fotoUrl = $fallbackFoto;
        }

        return $fotoUrl;
    }

    // Metodo para iniciar sesión
    public static function login($email, $password)
    {
        $UsuarioModelo = new UsuarioModelo();
        try {
            $usuario = $UsuarioModelo->getUsuarioByEmail($email);
            if ($usuario === null) {
                return ['error' => 'Usuario no encontrado'];
            }
            // Verifica si el usuario existe y si la contraseña es correcta para iniciar sesión
            if ($usuario && password_verify($password, $usuario['contraseña'])) {
                return ['id' => $usuario['idUsuario'], 'nombre' => $usuario['nombre'], 'foto_perfil' => $usuario['imagen']];
            } else {
                // Si las credenciales son incorrectas, devuelve un mensaje de error
                return ['error' => 'Credenciales incorrectas'];
            }
        } catch (Exception $e) {
            // Si hay un error en la base de datos, devuelve un mensaje de error
            return ['error' => $e->getMessage()];
        }
    }

    // Metodo para obtener la información de un usuario por su ID
    public static function getUser($id = null)
    {
        $UsuarioModelo = new UsuarioModelo();
        try {
            if ($id) {
                $usuario = $UsuarioModelo->getUsuarioById($id);
                $telefono = $UsuarioModelo->getUsuarioTelefonoById($id);
                $proveedor = $UsuarioModelo->getProveedorById($id);
                $habilidades = $UsuarioModelo->getProveedorHabilidadesById($id);
                $titulos = $UsuarioModelo->getProveedorTitulosById($id);

                if ($proveedor && $usuario) {
                    // Obtener el promedio de calificaciones del proveedor
                    $calificacionData = $UsuarioModelo->getPromedioCalificacionProveedor($id);
                    
                    return [
                        'id' => $proveedor['idProveedor'],
                        'nombre' => $usuario['nombre'],
                        'apellido' => $usuario['apellido'],
                        'email' => $usuario['email'],
                        'fecha_nacimiento' => $usuario['fechaNac'],
                        'foto_perfil' => $usuario['imagen'],
                        'telefono' => $telefono ? $telefono['telefono'] : null,
                        'nombre_empresa' => $proveedor['nombreEmpresa'],
                        'descripcion' => $proveedor['descripcion'],
                        'experiencia' => $proveedor['experiencia'],
                        'habilidades' => $habilidades,
                        'titulos' => $titulos,
                        'esProveedor' => true,
                        'registroMp' => self::verificarRegistroMp($proveedor),
                        'calificacionPromedio' => $calificacionData['promedio'],
                        'totalResenas' => $calificacionData['totalResenas'],
                ];
                } elseif ($usuario) {
                    // Si el usuario existe, devuelve su información
                    return [
                        'id' => $usuario['idUsuario'],
                        'nombre' => $usuario['nombre'],
                        'apellido' => $usuario['apellido'],
                        'email' => $usuario['email'],
                        'fecha_nacimiento' => $usuario['fechaNac'],
                        'foto_perfil' => $usuario['imagen'],
                        'telefono' => $telefono ? $telefono['telefono'] : null,
                        'esProveedor' => false,
                    ];
                } else {
                    // Si el usuario no existe, devuelve un mensaje de error
                    return ['error' => 'Usuario no encontrado'];
                }
            } else {
                return ['error' => 'ID de usuario no proporcionado'];
            }
        } catch (Exception $e) {
            // Si hay un error en la base de datos, devuelve un mensaje de error
            return ['error' => $e->getMessage()];
        }
    }

    // Método privado para verificar si el proveedor tiene registro en MercadoPago
    private static function verificarRegistroMp($proveedor)
    {
        if ($proveedor && !empty($proveedor['accesToken']) && !empty($proveedor['refreshToken'])) {
            return true;
        }

        return false;
    }

    public static function updateProfile($idUsuario, $nombre, $apellido, $email, $telefono, $foto_perfil, $esProveedor, $nombreEmpresa, $descripcion, $habilidades, $experiencia, $titulos)
    {
        try {
            $UsuarioModelo = new UsuarioModelo();

            // Verificar que el usuario existe
            $usuarioExistente = $UsuarioModelo->getUsuarioById($idUsuario);
            if ($usuarioExistente === null) {
                return ['error' => 'Usuario no encontrado'];
            }

            // Validar edad si se está actualizando (aunque normalmente fecha de nacimiento no se actualiza)
            $fechaNacimiento = $usuarioExistente['fechaNac'];
            $fechaNac = new DateTime($fechaNacimiento);
            $hoy = new DateTime();
            $edad = $hoy->diff($fechaNac)->y;

            if ($edad < 18) {
                return ['error' => 'El usuario debe tener al menos 18 años'];
            }

            if ($edad > 100) {
                return ['error' => 'La edad no puede ser mayor a 100 años'];
            }

            // Comentado temporalmente para debug
            /*
            // Solo moderar si se proporcionan datos nuevos
            if ($nombre !== null || $apellido !== null || $email !== null) {
                $textoModeracion = 'Nombre: ' . ($nombre ?? $usuarioExistente['nombre']) . ', Apellido: ' . ($apellido ?? $usuarioExistente['apellido']) . ', Email: ' . ($email ?? $usuarioExistente['email']);
                $Moderacion = IA::Moderacion($textoModeracion);
                if (isset($Moderacion['error'])) {
                    return ['error' => 'Error en la moderación de contenido'];
                }
                if ($Moderacion === "false") {
                    return ['error' => 'El contenido proporcionado no cumple con las políticas de uso.'];
                }
            }
            */

            // Usar valores existentes si no se proporcionan nuevos
            $nombre ??= $usuarioExistente['nombre'];
            $apellido ??= $usuarioExistente['apellido'];
            $email ??= $usuarioExistente['email'];

            // Solo subir nueva foto si se proporciona
            $fotoPerfil = $usuarioExistente['imagen'];
            if ($foto_perfil !== null && is_array($foto_perfil) && $foto_perfil['error'] === UPLOAD_ERR_OK) {
                $fotoPerfil = self::uploadFoto($foto_perfil, $nombre);
            }

            // Actualiza la información del usuario
            if ($telefono !== null) {
                $UsuarioModelo->eliminarTelefonoUsuario($idUsuario);
            }
            $actualizadoUsuario = $UsuarioModelo->updateUsuario($idUsuario, $nombre, $apellido, $email, $fotoPerfil, $telefono);

            $actualizadoProveedor = false;
            if (isset($esProveedor) && ($esProveedor === 'true' || $esProveedor === true || $esProveedor === 1 || $esProveedor === '1')) {
                // Actualiza la información del proveedor
                $proveedorExistente = $UsuarioModelo->getProveedorById($idUsuario);
                if (!$proveedorExistente) {
                    return ['error' => 'El usuario no es un proveedor'];
                }

                // Usar valores existentes si no se proporcionan nuevos
                $nombreEmpresa ??= $proveedorExistente['nombreEmpresa'];
                $descripcion ??= $proveedorExistente['descripcion'];
                $experiencia ??= $proveedorExistente['experiencia'];

                // Validar coherencia entre edad y experiencia si se proporciona experiencia
                if ($experiencia !== null && is_numeric($experiencia)) {
                    $experienciaAnios = intval($experiencia);
                    $maxExperiencia = $edad - 18;
                    if ($experienciaAnios > $maxExperiencia) {
                        return ['error' => 'La experiencia laboral (' . $experienciaAnios . ' años) no es coherente con la edad del usuario (' . $edad . ' años). La experiencia máxima posible sería ' . $maxExperiencia . ' años'];
                    }
                    if ($experienciaAnios < 0) {
                        return ['error' => 'La experiencia laboral debe ser un número positivo'];
                    }
                }

                // Comentado temporalmente para debug
                $ModeracionProveedor = IA::Moderacion('Nombre Empresa: ' . $nombreEmpresa . ', Descripción: ' . $descripcion);
                if (isset($ModeracionProveedor['error'])) {
                    return ['error' => 'Error en la moderación de contenido del proveedor'];
                }
                if ($ModeracionProveedor === 'false') {
                    return ['error' => 'El contenido proporcionado para el proveedor no cumple con las políticas de uso.'];
                }

                // Log para debug
                error_log("Actualizando proveedor - ID: $idUsuario, Empresa: $nombreEmpresa, Descripcion: $descripcion, Experiencia: $experiencia");

                $actualizadoProveedor = $UsuarioModelo->updateProveedor($idUsuario, $nombreEmpresa, $descripcion, $experiencia);

                if (!$actualizadoProveedor) {
                    error_log('ERROR: updateProveedor devolvió false');

                    return ['error' => 'No se pudo actualizar la información del proveedor'];
                }

                // Actualizar habilidades
                if ($habilidades !== null && is_array($habilidades)) {
                    error_log('Actualizando habilidades: ' . json_encode($habilidades));
                    $UsuarioModelo->eliminarProveedorHabilidades($idUsuario);
                    foreach ($habilidades as $habilidad) {
                        if (!empty($habilidad)) {
                            $UsuarioModelo->crearProveedorHabilidad($idUsuario, $habilidad);
                        }
                    }
                }

                // Actualizar títulos
                if ($titulos !== null && is_array($titulos)) {
                    error_log('Actualizando títulos: ' . json_encode($titulos));
                    $UsuarioModelo->eliminarProveedorTitulos($idUsuario);
                    foreach ($titulos as $titulo) {
                        if (!empty($titulo)) {
                            $UsuarioModelo->crearProveedorTitulo($idUsuario, $titulo);
                        }
                    }
                }
            }

            if ($actualizadoUsuario || $actualizadoProveedor) {
                return ['success' => true, 'mensaje' => 'Perfil actualizado correctamente'];
            } else {
                return ['error' => 'No se pudo actualizar el perfil'];
            }
        } catch (Exception $e) {
            error_log('Error en updateProfile: ' . $e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    public static function updatePassword($idUsuario, $passwordActual, $nuevoPassword)
    {
        try {
            $UsuarioModelo = new UsuarioModelo();
            $usuario = $UsuarioModelo->getUsuarioById($idUsuario);
            if ($usuario === null) {
                return ['error' => 'Usuario no encontrado'];
            }
            // Verifica si la contraseña actual es correcta
            if (password_verify($passwordActual, $usuario['contraseña'])) {
                // Encripta la nueva contraseña
                $nuevoPasswordHash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
                // Actualiza la contraseña en la base de datos
                $actualizado = $UsuarioModelo->updateUsuarioPassword($idUsuario, $nuevoPasswordHash);
                if ($actualizado) {
                    return ['success' => true];
                } else {
                    return ['error' => 'No se pudo actualizar la contraseña'];
                }
            } else {
                return ['error' => 'La contraseña actual es incorrecta'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
