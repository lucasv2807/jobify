<?php

require_once 'src/model/Servicio.php';
require_once 'src/model/Usuario.php';
require_once 'src/controller/Resena.php';
require_once 'src/controller/Usuario.php';
require_once 'src/utils/IA.php';

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Format;
use Cloudinary\Transformation\Quality;
use Cloudinary\Transformation\AspectRatio;
use Cloudinary\Configuration\Configuration;

class ServicioControlador
{
    public static function crear($idProveedor, $nombre, $descripcion, $categorias, $ubicaciones, $precio, $fotosServicio, $diasDisponibles)
    {
        // Validar precio
        if (!is_numeric($precio) || floatval($precio) < 0) {
            return ['error' => 'El precio no puede ser negativo'];
        }
        if (floatval($precio) > 500000) {
            return ['error' => 'Si desea publicar un servicio más caro de lo permitido contáctanos: clutchsoftware2025@gmail.com'];
        }

        $Moderacion = IA::Moderacion('Nombre: ' . $nombre . ', Descripción: ' . $descripcion);
        if (isset($Moderacion['error'])) {
            return ['error' => 'Error en la moderación de contenido'];
        }
        if ($Moderacion === 'false') {
            return ['error' => 'El contenido proporcionado no cumple con las políticas de uso.'];
        }
        $ServicioModelo = new ServicioModelo();
        $UsuarioModelo = new UsuarioModelo();
        try {
            $proveedor = $UsuarioModelo->getProveedorById($idProveedor);
            if ($proveedor === null) {
                return ['error' => 'Proveedor no encontrado'];
            }

            $id = $ServicioModelo->crearServicio($idProveedor, $nombre, $descripcion, $precio);

            if ($id !== null) {
                for ($i = 0; $i < count($fotosServicio['name']); $i++) {
                    $fotoServicio = [];

                    $fotoServicio['tmp_name'] = $fotosServicio['tmp_name'][$i];
                    $fotoServicio['error'] = $fotosServicio['error'][$i];
                    $fotoServicio['name'] = $fotosServicio['name'][$i];
                    $fotoServicio['type'] = $fotosServicio['type'][$i];
                    $fotoServicio['size'] = $fotosServicio['size'][$i];

                    $fotoUrl = self::uploadFoto($fotoServicio);

                    if ($fotoUrl) {
                        $ServicioModelo->crearFotoServicio($id, $fotoUrl);
                    }
                }
                foreach ($categorias as $categoria) {
                    $ServicioModelo->crearCategorias($id, $categoria);
                }
                foreach ($diasDisponibles as $dia) {
                    $ServicioModelo->crearDiasDisponibles($id, $dia);
                }
                foreach ($ubicaciones as $ubicacion) {
                    $ServicioModelo->crearUbicacion($id, $ubicacion);
                }

                return ['id' => $id];
            } else {
                return ['error' => 'No se pudo crear el servicio'];
            }
        } catch (Exception $e) {
            return ['error' => 'Error al crear el servicio: ' . $e->getMessage()];
        }
    }

    private static function uploadFoto($fotoServicio)
    {
        if ($fotoServicio === null) {
            return ['error' => 'Error imagen no proporcionada'];
        }
        $config = Configuration::fromCloudinaryUrl($_ENV['CLOUDINARY_URL']);
        $cloudinary = new Cloudinary($config);
        if (is_array($fotoServicio) && $fotoServicio['error'] === UPLOAD_ERR_OK) {
            try {
                $upload = $cloudinary->uploadApi()->upload($fotoServicio['tmp_name'], [
                    'folder' => 'jobify/servicios',
                    'public_id' => uniqid('servicio_'),
                ]);
                $publicId = $upload['public_id'];
                $fotoUrl = $cloudinary->image($publicId)
                    ->resize(Resize::scale()->width(1280)->height(720)->aspectRatio(AspectRatio::ar16x9()))
                    ->quality(Quality::auto())
                    ->format(Format::auto())
                    ->toUrl();
            } catch (Exception $e) {
                return ['error' => 'Error al subir la foto: ' . $e->getMessage()];
            }
        } else {
            return ['error' => 'Error al subir la foto'];
        }

        return $fotoUrl;
    }

    public static function getServicio(string | null $id = null)
    {
        $ServicioModelo = new ServicioModelo();
        try {
            if ($id !== null) {
                $servicio = $ServicioModelo->getServicioById($id);
                $imagenes = array_column($ServicioModelo->getImagenesById($id), 'imagen');
                $categorias = array_column($ServicioModelo->getCategoriasById($id), 'categoria');
                $ubicaciones = array_column($ServicioModelo->getUbicacionesById($id), 'ubicacion');
                $diasDisponibles = array_column($ServicioModelo->getDiasDisponiblesById($id), 'diaDisponible');
                if ($servicio) {
                    return [
                        'id' => $servicio['idServicio'],
                        'nombre' => $servicio['titulo'],
                        'precio' => $servicio['precio'],
                        'idProveedor' => $servicio['idProveedor'],
                        'descripcion' => $servicio['descripcion'],
                        'imagenes' => $imagenes,
                        'categorias' => $categorias,
                        'ubicaciones' => $ubicaciones,
                        'diasDisponibles' => $diasDisponibles,
                        'nombreProveedor' => $servicio['nombreProveedor'],
                        'apellidoProveedor' => $servicio['apellidoProveedor'],
                        'imagenProveedor' => $servicio['imagenProveedor'],
                    ];
                } else {
                    return ['error' => 'Servicio no encontrado'];
                }
            } elseif ($id === null) {
                $servicios = $ServicioModelo->getServicios();
                if ($servicios) {
                    return array_map(function ($servicio) {
                        $ServicioModelo = new ServicioModelo();
                        $resenaController = new resenaControlador();
                        $UsuarioControlador = new UsuarioControlador();

                        // Use the correct IDs: idServicio for service-related lookups,
                        // idProveedor for provider/user lookups (if available)
                        $idServicio = $servicio['idServicio'];
                        $idProveedor = $servicio['idProveedor'] ?? null;

                        $imagenes = array_column($ServicioModelo->getImagenesById($idServicio), 'imagen');
                        $categorias = array_column($ServicioModelo->getCategoriasById($idServicio), 'categoria');
                        $ubicaciones = array_column($ServicioModelo->getUbicacionesById($idServicio), 'ubicacion');
                        $diasDisponibles = array_column($ServicioModelo->getDiasDisponiblesById($idServicio), 'diaDisponible');

                        $nombreProveedor = $idProveedor ? ($UsuarioControlador->getUser($idProveedor)['nombre'] ?? null) : null;

                        // Get reviews and compute average safely (model returns arrays with 'calificacion')
                        $resenas = $resenaController->getresena($idServicio);
                        $resenaPromedio = null;
                        $numeroResenas = 0;
                        if (is_array($resenas) && !empty($resenas)) {
                            $sum = 0.0;
                            $count = 0;
                            foreach ($resenas as $r) {
                                $val = isset($r['calificacion']) ? (float) $r['calificacion'] : null;
                                if ($val !== null) {
                                    $sum += $val;
                                    $count++;
                                }
                            }
                            if ($count > 0) {
                                $resenaPromedio = $sum / $count;
                                $numeroResenas = $count;
                            }
                        }

                        return [
                            'id' => $servicio['idServicio'],
                            'nombre' => $servicio['titulo'],
                            'precio' => $servicio['precio'],
                            'imagen' => $imagenes ? $imagenes[0] : [],
                            'categorias' => $categorias,
                            'ubicaciones' => $ubicaciones,
                            'diasDisponibles' => $diasDisponibles,
                            'calificacion' => $resenaPromedio,
                            'calificaciones' => $numeroResenas,
                            'nombre_proveedor' => $nombreProveedor,
                        ];
                    }, $servicios);
                } else {
                    return ['error' => 'No se encontraron servicios'];
                }
            }
        } catch (Exception $e) {
            return ['error' => 'Error al obtener el servicio: ' . $e->getMessage()];
        }
    }

    public static function borrar($idServicio, $idProveedor)
    {
        $ServicioModelo = new ServicioModelo();
        $UsuarioModelo = new UsuarioModelo();
        try {
            // Validar que el proveedor existe
            $proveedor = $UsuarioModelo->getProveedorById($idProveedor);
            if (!$proveedor) {
                return ['error' => 'Proveedor no encontrado'];
            }
            
            // Validar que el servicio existe y que pertenece a este proveedor
            $servicio = $ServicioModelo->getServicioById($idServicio);
            if (!$servicio) {
                return ['error' => 'Servicio no encontrado'];
            }
            
            // Comparamos como enteros para evitar problemas de tipo (string vs int)
            if ((int) $servicio['idProveedor'] !== (int) $idProveedor) {
                return ['error' => 'No tienes permisos para borrar este servicio'];
            }
            
            $ServicioModelo-> eliminarCategorias($idServicio);
            $ServicioModelo-> eliminarUbicaciones($idServicio);
            $ServicioModelo-> eliminarDiasDisponibles($idServicio);
            $ServicioModelo-> eliminarImagenes($idServicio);
            $response = $ServicioModelo->borrarServicio($idServicio, $idProveedor);
            return $response;
        } catch (Exception $e) {
            return ['error' => 'Error al borrar el servicio: ' . $e->getMessage()];
        }
    }

    public static function search($query)
    {
        $ServicioModelo = new ServicioModelo();
        try {
            $services = $ServicioModelo->searchServicios($query);
            $result = [];
            if (!$services) {
                return ['result' => 'No se encontraron servicios que coincidan con la búsqueda'];
            } else {
                foreach ($services as $service) {
                    $serCats = array_column($ServicioModelo->getCategoriasById($service['idServicio']), 'categoria');
                    $serUbi = array_column($ServicioModelo->getUbicacionesById($service['idServicio']), 'ubicacion');
                    $serDias = array_column($ServicioModelo->getDiasDisponiblesById($service['idServicio']), 'diaDisponible');
                    $serImgs = array_column($ServicioModelo->getImagenesById($service['idServicio']), 'imagen');
                    $result[] = [
                        'id' => $service['idServicio'],
                        'nombre' => $service['titulo'],
                        'precio' => $service['precio'],
                        'descripcion' => $service['descripcion'],
                        'categorias' => $serCats,
                        'ubicaciones' => $serUbi,
                        'diasDisponibles' => $serDias,
                        'imagenes' => $serImgs ? $serImgs[0] : [],
                    ];
                }
            }

            return $result;
        } catch (Exception $e) {
            return ['error' => 'Error al buscar servicios: ' . $e->getMessage()];
        }
    }

    public static function updateServicio($idServicio, $nombre = null, $descripcion = null, $categorias = null, $ubicaciones = null, $precio = null, $fotosServicio = null, $diasDisponibles = null, $imagenesExistentes = null)
    {
        // Validar precio si se proporciona
        if ($precio !== null) {
            if (!is_numeric($precio) || floatval($precio) < 0) {
                return ['error' => 'El precio no puede ser negativo'];
            }
            if (floatval($precio) > 500000) {
                return ['error' => 'Si desea publicar un servicio más caro de lo permitido contáctanos: clutchsoftware2025@gmail.com'];
            }
        }

        $Moderacion = IA::Moderacion('Nombre: ' . $nombre . ', Descripción: ' . $descripcion);
        if (isset($Moderacion['error'])) {
            return ['error' => 'Error en la moderación de contenido'];
        }
        if ($Moderacion === 'false') {
            return ['error' => 'El contenido proporcionado no cumple con las políticas de uso.'];
        }
        $ServicioModelo = new ServicioModelo();
        try {
            $servicioExistente = $ServicioModelo->getServicioById($idServicio);
            if (!$servicioExistente) {
                return ['error' => 'Servicio no encontrado'];
            }

            $ServicioModelo->actualizarServicio($idServicio, $nombre, $descripcion, $precio);

            if ($categorias !== null) {
                $ServicioModelo->eliminarCategorias($idServicio);
                foreach ($categorias as $categoria) {
                    $ServicioModelo->crearCategorias($idServicio, $categoria);
                }
            }

            if ($ubicaciones !== null) {
                $ServicioModelo->eliminarUbicaciones($idServicio);
                foreach ($ubicaciones as $ubicacion) {
                    $ServicioModelo->crearUbicacion($idServicio, $ubicacion);
                }
            }

            if ($diasDisponibles !== null) {
                $ServicioModelo->eliminarDiasDisponibles($idServicio);
                foreach ($diasDisponibles as $dia) {
                    $ServicioModelo->crearDiasDisponibles($idServicio, $dia);
                }
            }

            // Manejar actualización de imágenes
            // Si se proporciona imagenesExistentes o fotosServicio, actualizar imágenes
            if ($imagenesExistentes !== null || ($fotosServicio !== null && isset($fotosServicio['name']) && !empty($fotosServicio['name'][0]))) {
                // Eliminar todas las imágenes actuales
                $ServicioModelo->eliminarImagenes($idServicio);

                // Re-insertar las imágenes existentes que se deben mantener
                if ($imagenesExistentes !== null && is_array($imagenesExistentes)) {
                    foreach ($imagenesExistentes as $imagenUrl) {
                        if (!empty($imagenUrl)) {
                            $ServicioModelo->crearFotoServicio($idServicio, $imagenUrl);
                        }
                    }
                }

                // Subir y agregar nuevas imágenes
                if ($fotosServicio !== null && isset($fotosServicio['name']) && is_array($fotosServicio['name'])) {
                    for ($i = 0; $i < count($fotosServicio['name']); $i++) {
                        // Verificar que hay un archivo válido en esta posición
                        if (!empty($fotosServicio['name'][$i]) && $fotosServicio['error'][$i] === UPLOAD_ERR_OK) {
                            $fotoServicio = [
                                'tmp_name' => $fotosServicio['tmp_name'][$i],
                                'error' => $fotosServicio['error'][$i],
                                'name' => $fotosServicio['name'][$i],
                                'type' => $fotosServicio['type'][$i],
                                'size' => $fotosServicio['size'][$i],
                            ];

                            $fotoUrl = self::uploadFoto($fotoServicio);

                            if ($fotoUrl && !isset($fotoUrl['error'])) {
                                $ServicioModelo->crearFotoServicio($idServicio, $fotoUrl);
                            }
                        }
                    }
                }
            }

            return ['success' => 'Servicio actualizado correctamente'];
        } catch (Exception $e) {
            return ['error' => 'Error al actualizar el servicio: ' . $e->getMessage()];
        }
    }

    public static function searchByIA($texto)
    {
        try {
            $servicios = self::getServicio();
            if (!$servicios) {
                return ['error' => 'No se encontraron servicios disponibles'];
            }
            $res = IA::ChatIA($servicios, $texto);

            return $res;
        } catch (Exception $e) {
            return ['error' => 'Error al obtener los servicios del proveedor: ' . $e->getMessage()];
        }
    }

    public static function getServiciosByProveedor($idProveedor)
    {
        $ServicioModelo = new ServicioModelo();
        try {
            $services = $ServicioModelo->getServiciosByProveedor($idProveedor);
            $result = [];
            if (!$services) {
                return ['result' => 'No se encontraron servicios para el proveedor dado'];
            } else {
                foreach ($services as $service) {
                    $serCats = array_column($ServicioModelo->getCategoriasById($service['idServicio']), 'categoria');
                    $serUbi = array_column($ServicioModelo->getUbicacionesById($service['idServicio']), 'ubicacion');
                    $serDias = array_column($ServicioModelo->getDiasDisponiblesById($service['idServicio']), 'diaDisponible');
                    $serImgs = array_column($ServicioModelo->getImagenesById($service['idServicio']), 'imagen');
                    $result[] = [
                        'id' => $service['idServicio'],
                        'nombre' => $service['titulo'],
                        'precio' => $service['precio'],
                        'descripcion' => $service['descripcion'],
                        'categorias' => $serCats,
                        'ubicaciones' => $serUbi,
                        'diasDisponibles' => $serDias,
                        'imagenes' => $serImgs ? $serImgs[0] : [],
                    ];
                }
            }

            return $result;
        } catch (Exception $e) {
            return ['error' => 'Error al obtener los servicios del proveedor: ' . $e->getMessage()];
        }
    }

    public static function saveFavorito($idServicio, $idCliente)
    {
        $ServicioModelo = new ServicioModelo();
        try {
            $exists = $ServicioModelo->favoritoExists($idServicio, $idCliente);
            if ($exists) {
                return ['error' => 'El servicio ya está en favoritos'];
            }
            $ServicioModelo->saveFavorito($idServicio, $idCliente);

            return ['success' => 'Servicio guardado en favoritos'];
        } catch (Exception $e) {
            return ['error' => 'Error al guardar el favorito: ' . $e->getMessage()];
        }
    }

    public static function deleteFavorito($idServicio, $idCliente)
    {
        $ServicioModelo = new ServicioModelo();
        try {
            $exists = $ServicioModelo->favoritoExists($idServicio, $idCliente);
            if (!$exists) {
                return ['error' => 'El servicio no está en favoritos'];
            }
            $ServicioModelo->deleteFavorito($idServicio, $idCliente);

            return ['success' => 'Servicio eliminado de favoritos'];
        } catch (Exception $e) {
            return ['error' => 'Error al eliminar el favorito: ' . $e->getMessage()];
        }
    }

    public static function getFavoritosByCliente($idCliente)
    {
        $ServicioModelo = new ServicioModelo();
        $UsuarioModelo = new UsuarioModelo();
        if (!$UsuarioModelo->getUsuarioById($idCliente)) {
            return ['error' => 'Cliente no encontrado'];
        }
        // detectar si el usuario es cliente
        if (!$UsuarioModelo->esCliente($idCliente)) {
            return ['error' => 'El usuario no es un cliente'];
        }
        try {
            $favoritos = $ServicioModelo->getFavoritosByCliente($idCliente);
            $result = [];
            if (!$favoritos) {
                return ['isFavorito' => false, 'result' => 'No se encontraron servicios favoritos para el cliente dado'];
            } else {
                foreach ($favoritos as $fav) {
                    $serCats = array_column($ServicioModelo->getCategoriasById($fav['idServicio']), 'categoria');
                    $serUbi = array_column($ServicioModelo->getUbicacionesById($fav['idServicio']), 'ubicacion');
                    $serDias = array_column($ServicioModelo->getDiasDisponiblesById($fav['idServicio']), 'diaDisponible');
                    $serImgs = array_column($ServicioModelo->getImagenesById($fav['idServicio']), 'imagen');
                    $result[] = [
                        'isFavorito' => true,
                        'id' => $fav['idServicio'],
                        'nombre' => $fav['titulo'],
                        'precio' => $fav['precio'],
                        'descripcion' => $fav['descripcion'],
                        'categorias' => $serCats,
                        'ubicaciones' => $serUbi,
                        'diasDisponibles' => $serDias,
                        'imagenes' => $serImgs ? $serImgs[0] : [],
                    ];
                }
            }

            return $result;
        } catch (Exception $e) {
            return ['error' => 'Error al obtener los servicios favoritos del cliente: ' . $e->getMessage()];
        }
    }
}
