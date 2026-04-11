<?php

require_once 'src/model/Resena.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Format;
use Cloudinary\Transformation\Quality;

class resenaControlador
{
    // Metodo para crear una nueva resena
    public static function crearresena($idCliente, $idServicio, $calificacion, $comentario, $imagen)
    {
        try {
            // Validar datos obligatorios primero
            if (empty($idCliente) || empty($idServicio) || $calificacion === '' || $calificacion === null) {
                return ['error' => 'Faltan datos obligatorios'];
            }

            // Verifica que la calificacion este entre 1 y 5
            if ($calificacion < 1 || $calificacion > 5) {
                return ['error' => 'La calificación debe estar entre 1 y 5'];
            }

            // Intentar subir la imagen (puede ser null si no se envió o hubo error)
            $imagenUrl = null;
            if ($imagen !== null) {
                $imagenUrl = self::uploadImagen($imagen);
                // Si se intentó subir una imagen pero falló, retornar error
                if ($imagenUrl === null) {
                    return ['error' => 'Error al subir la imagen'];
                }
            }

            $resenaModelo = new resenaModelo();

            // Crea la resena en la base de datos
            $idResena = $resenaModelo->crearresena($calificacion, $comentario, $imagenUrl);
            if ($idResena) {
                $publica = $resenaModelo->crearPublica($idCliente, $idServicio, $idResena);
                if ($publica) {
                    return ['id' => $idResena];
                } else {
                    return ['error' => 'No se pudo asociar la resena'];
                }
            } else {
                return ['error' => 'No se pudo crear la resena'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function uploadImagen($imagen)
    {
        // Default return value
        $imagenUrl = null;

        // Ensure CLOUDINARY_URL can be read either from $_ENV or getenv
        $cloudinaryUrl = $_ENV['CLOUDINARY_URL'] ?? getenv('CLOUDINARY_URL');
        if (empty($cloudinaryUrl)) {
            // Can't upload without configuration
            error_log('uploadImagen: CLOUDINARY_URL not set');

            return null;
        }

        $config = Configuration::fromCloudinaryUrl($cloudinaryUrl);
        $cloudinary = new Cloudinary($config);

        // Validate incoming file structure
        if (!is_array($imagen) || !isset($imagen['error']) || $imagen['error'] !== UPLOAD_ERR_OK) {
            // Not a valid uploaded file or there was an upload error
            return null;
        }

        try {
            $upload = $cloudinary->uploadApi()->upload($imagen['tmp_name'], [
                'folder' => 'jobify/reseñas',
                'public_id' => uniqid('resena_'),
            ]);

            if (isset($upload['public_id'])) {
                $publicId = $upload['public_id'];
                $imagenUrl = $cloudinary->image($publicId)
                    ->resize(Resize::limitFit()->width(800)->height(800))
                    ->quality(Quality::auto())
                    ->format(Format::auto())
                    ->toUrl();
            }
        } catch (Exception $e) {
            // Log the error and return null so caller can handle it consistently
            error_log('Error al subir imagen a Cloudinary: ' . $e->getMessage());

            return null;
        }

        return $imagenUrl;
    }

    public static function getresena($idServicio)
    {
        try {
            $resenaModelo = new resenaModelo();
            $resenas = $resenaModelo->getresenasByServicio($idServicio);

            return $resenas;
        } catch (Exception $e) {
            return ['error' => 'Error al obtener las resenas: ' . $e->getMessage()];
        }
    }
}
