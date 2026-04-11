<?php

require_once 'src/model/Usuario.php';
require_once 'src/model/Admin.php';
require_once 'src/controller/Resena.php';

class AdminControlador
{
    public static function verifyAdminPassword($password)
    {
        $adminPassword = getenv('ADMIN_PASSWORD');
        if ($adminPassword === false || $adminPassword === '') {
            error_log('ADMIN_PASSWORD no está configurada en el entorno');
            return ['success' => false, 'message' => 'Configuración de administrador no disponible'];
        }

        if (hash_equals($adminPassword, $password)) {
            return ['success' => true, 'message' => 'Acceso concedido'];
        }

        return ['success' => false, 'message' => 'Contraseña incorrecta'];
    }

    public static function register($nombre, $apellido, $email, $password, $fecha_nacimiento, $tipo)
    {
        try {
            $foto = 'https://res.cloudinary.com/doh46tbdz/image/upload/v1761794912/jobify/usuarios/perfil_6902db5e988ca.jpg';
            $telefono = '099 999 999';
            $nombreEmpresa = 'empresa default';
            $descripcion = 'descripcion default';
            $experiencia = 0;
            $habilidades = 'vacio';
            $titulos = 'vacio';
            $password = password_hash($password, PASSWORD_DEFAULT);

            $hoy = date('Y-m-d');

            switch ($tipo) {
                case 'Moderador':
                    $id = UsuarioModelo::crearUsuario($nombre, $apellido, $email, $password, $fecha_nacimiento, $foto, $telefono);
                    AdminModelo::crearModerador($id, $hoy);

                    return ['id' => $id];
                    break;
                case 'Proveedor':
                    $id = UsuarioModelo::crearUsuarioProveedor($nombre, $apellido, $email, $password, $fecha_nacimiento, $foto, $telefono, $nombreEmpresa, $descripcion, $experiencia, $habilidades, $titulos);

                    return ['id' => $id];
                    break;
                case 'Cliente':
                    $id = UsuarioModelo::crearUsuario($nombre, $apellido, $email, $password, $fecha_nacimiento, $foto, $telefono);
                    UsuarioModelo::crearCliente($id);

                    return ['id' => $id];
                    break;
                default:
                    return ['error' => 'Tipo de usuario no válido'];
            }
        } catch (Exception $e) {
            error_log('Error en AdminControlador::register: ' . $e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }
    public static function getUserList()
    {
        try {
            $users = AdminModelo::fetchAllUsers();
            if (!$users) {
                return ['error' => 'No se encontraron usuarios'];
            }
            return $users;
        } catch (Exception $e) {
            error_log('Error en AdminControlador::getUserList: ' . $e->getMessage());
            return ['error' => 'Error al obtener la lista de usuarios'];
        }
    }
    public static function deleteUser($id)
    {
        try {
            AdminModelo::deleteUser($id);
            return ['message' => 'Usuario eliminado exitosamente'];
        } catch (Exception $e) {
            error_log('Error en AdminControlador::deleteUser: ' . $e->getMessage());
            return ['error' => 'Error al eliminar el usuario'];
        }
    }
    public static function getServiceList()
    {
        try {
            $services = AdminModelo::fetchAllServices();
            if (!$services) {
                return ['error' => 'No se encontraron servicios'];
            }
            return $services;
        } catch (Exception $e) {
            error_log('Error en AdminControlador::getServiceList: ' . $e->getMessage());
            return ['error' => 'Error al obtener la lista de servicios'];
        }
    }
    public static function deleteService($id)
    {
        try {
            AdminModelo::deleteService($id);
            return ['message' => 'Servicio eliminado exitosamente'];
        } catch (Exception $e) {
            error_log('Error en AdminControlador::deleteService: ' . $e->getMessage());
            return ['error' => 'Error al eliminar el servicio'];
        }
    }
    public static function getReviewList()
    {
        try {
            $reviews = AdminModelo::fetchAllReviews();
            if (!$reviews) {
                return ['error' => 'No se encontraron reseñas'];
            }
            return $reviews;
        } catch (Exception $e) {
            error_log('Error en AdminControlador::getReviewList: ' . $e->getMessage());
            return ['error' => 'Error al obtener la lista de reseñas'];
        }
    }
    public static function deleteReview($id)
    {
        try {
            AdminModelo::deleteReview($id);
            return ['message' => 'Reseña eliminada exitosamente'];
        } catch (Exception $e) {
            error_log('Error en AdminControlador::deleteReview: ' . $e->getMessage());
            return ['error' => 'Error al eliminar la reseña'];
        }
    }
    public static function reviewModify($id, $comentario, $calificacion, $imagen)
    {
        try {
            // imagen puede ser null
            $imagenUrl = null;
            if ($imagen !== null && isset($imagen['error']) && $imagen['error'] === UPLOAD_ERR_OK) {
                $imagenUrl = resenaControlador::uploadImagen($imagen);
                if ($imagenUrl === null) {
                    return ['error' => 'Error al subir la imagen'];
                }
            }
            AdminModelo::modifyReview($id, $comentario, $calificacion, $imagenUrl);
            return ['message' => 'Reseña modificada exitosamente'];
        } catch (Exception $e) {
            error_log('Error en AdminControlador::reviewModify: ' . $e->getMessage());
            return ['error' => 'Error al modificar la reseña'];
        }
    }
}
