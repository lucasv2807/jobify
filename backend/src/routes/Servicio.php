<?php

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);
$Ruta = explode('/', $request);
$method = $_SERVER['REQUEST_METHOD'];
require_once 'src/controller/Servicio.php';
require_once 'src/utils/Response.php';
require_once 'src/utils/blackList.php';
switch ($method) {
    case 'POST':
        if (isset($Ruta[2]) && $Ruta[2] == 'crear') {
            $create = new ServicioControlador();
            $idProveedor = $_POST['idProveedor'];
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $categoria = $_POST['categoria'];
            $ubicacion = $_POST['ubicacion'];
            $precio = $_POST['precio'];
            $fotoServicio = $_FILES['fotoServicio'];
            $diasDisponibles = $_POST['diasDisponibles'];
            if (!isset($idProveedor, $nombre, $descripcion, $categoria, $ubicacion, $precio, $fotoServicio, $diasDisponibles)) {
                Response::error('Faltan datos', 400);
            } else {
                // Validar palabras prohibidas
                try {
                    BlackList::validarTexto($nombre, 'nombre del servicio');
                    BlackList::validarTexto($descripcion, 'descripción del servicio');
                    BlackList::validarTexto($ubicacion, 'ubicación');
                } catch (Exception $e) {
                    Response::error($e->getMessage(), 400);
                }

                $data = $create->crear($idProveedor, $nombre, $descripcion, $categoria, $ubicacion, $precio, $fotoServicio, $diasDisponibles);
                if (isset($data['error'])) {
                    Response::error('Error al crear el servicio', 500, $data['error']);
                } else {
                    Response::success($data, 'Servicio creado exitosamente', 201);
                }
            }
        }
        if (isset($Ruta[2]) && $Ruta[2] == 'updateServicio') {
            // Código para actualizar un servicio
            $updateServicio = new ServicioControlador();
            $idServicio = $_POST['idServicio'] ?? null;
            $nombre = $_POST['nombre'] ?? null;
            $descripcion = $_POST['descripcion'] ?? null;
            $categoria = $_POST['categoria'] ?? null;
            $ubicacion = $_POST['ubicacion'] ?? null;
            $precio = $_POST['precio'] ?? null;
            $fotoServicio = $_FILES['fotoServicio'] ?? null;
            $imagenesExistentes = $_POST['imagenesExistentes'] ?? null;
            $diasDisponibles = $_POST['diasDisponibles'] ?? null;
            if (!isset($idServicio)) {
                Response::error('Faltan datos', 400, 'El parámetro idServicio es requerido');
            } else {
                // Validar palabras prohibidas en campos que se están actualizando
                try {
                    if (isset($nombre) && !empty($nombre)) {
                        BlackList::validarTexto($nombre, 'nombre del servicio');
                    }
                    if (isset($descripcion) && !empty($descripcion)) {
                        BlackList::validarTexto($descripcion, 'descripción del servicio');
                    }
                    if (isset($ubicacion) && !empty($ubicacion)) {
                        BlackList::validarTexto($ubicacion, 'ubicación');
                    }
                } catch (Exception $e) {
                    Response::error($e->getMessage(), 400);
                }

                $data = $updateServicio->updateServicio($idServicio, $nombre, $descripcion, $categoria, $ubicacion, $precio, $fotoServicio, $diasDisponibles, $imagenesExistentes);
                if (isset($data['error'])) {
                    Response::error('Error al actualizar el servicio', 500, $data['error']);
                } else {
                    Response::success($data, 'Servicio actualizado exitosamente', 200);
                }
            }
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'saveFavorito') {
            $ServicioControlador = new ServicioControlador();
            $idServicio = $_POST['idServicio'];
            $idCliente = $_POST['idCliente'];
            if (!isset($idServicio, $idCliente)) {
                Response::error('Faltan datos', 400, 'Los parámetros idServicio e idCliente son requeridos');
            } else {
                $data = $ServicioControlador->saveFavorito($idServicio, $idCliente);
                if (isset($data['error'])) {
                    Response::error('Error al guardar el favorito', 500, $data['error']);
                } else {
                    Response::success($data, 'Favorito guardado exitosamente', 200);
                }
            }
            if (isset($data['error'])) {
                Response::error('Error al listar servicios', 500, $data['error']);
            } else {
                Response::success(['response' => $data], 'Servicios listados exitosamente', 200);
            }
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'eliminarFavorito') {
            $ServicioControlador = new ServicioControlador();
            $idServicio = $_POST['idServicio'];
            $idCliente = $_POST['idCliente'];
            if (!isset($idServicio, $idCliente)) {
                Response::error('Faltan datos', 400, 'Los parámetros idServicio e idCliente son requeridos');
            } else {
                $data = $ServicioControlador->deleteFavorito($idServicio, $idCliente);
                if (isset($data['error'])) {
                    Response::error('Error al eliminar el favorito', 500, $data['error']);
                } else {
                    Response::success($data, 'Favorito eliminado exitosamente', 200);
                }
            }
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'borrar') {
            $deleteServicio = new ServicioControlador();
            $idServicio = $_POST['idServicio'];
            $idProveedor = $_POST['idProveedor'];
            if (!isset($idServicio, $idProveedor)) {
                Response::error('Faltan datos', 400, 'Los parámetros idServicio e idProveedor son requeridos');
            } else {
                $data = $deleteServicio->borrar($idServicio, $idProveedor);
                if (isset($data['error'])) {
                    Response::error('Error al borrar el servicio', 500, $data['error']);
                } else {
                    Response::success($data, 'Servicio eliminado exitosamente', 200);
                }
            }
        } else {
            Response::notFound();
        }
        break;
    case 'GET':
        if (isset($Ruta[2]) && $Ruta[2] == 'getServicio') {
            $getServicio = new ServicioControlador();
            $id = $_GET['id'] ?? null;
            $data = $getServicio->getServicio($id);
            if ($data === null) {
                Response::error('Servicio no encontrado', 404);
            } elseif (isset($data['error'])) {
                Response::error('Error al obtener el servicio', 500, $data['error']);
            } else {
                Response::success($data, 'Servicio obtenido exitosamente', 200);
            }
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'search') {
            $search = new ServicioControlador();
            $query = $_GET['q'];
            if (!isset($query)) {
                Response::error('Falta el parámetro de búsqueda', 400);
            } else {
                $data = $search->search($query);
                if (isset($data['error'])) {
                    Response::error('Error al buscar servicios', 500, $data['error']);
                } else {
                    Response::success($data, 'Búsqueda realizada exitosamente', 200);
                }
            }
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'IA') {
            $IA = new ServicioControlador();
            $texto = $_GET['pregunta'];
            $data = $IA->searchByIA($texto);
            if (isset($data['error'])) {
                Response::error('Error al listar servicios', 500, $data['error']);
            } else {
                Response::success(['response' => $data], 'Servicios listados exitosamente', 200);
            }
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'getServiciosByProveedor') {
            $getServicios = new ServicioControlador();
            $idProveedor = $_GET['idProveedor'] ?? null;
            $data = $getServicios->getServiciosByProveedor($idProveedor);
            if (isset($data['error'])) {
                Response::error('Error al listar servicios', 500, $data['error']);
            } else {
                Response::success(['response' => $data], 'Servicios listados exitosamente', 200);
            }
        } elseif (isset($Ruta[2]) && $Ruta[2] == 'getFavoritos') {
            $getFavoritos = new ServicioControlador();
            $idCliente = $_GET['idCliente'] ?? null;
            $data = $getFavoritos->getFavoritosByCliente($idCliente);
            if (isset($data['error'])) {
                Response::error('Error al listar favoritos', 500, $data['error']);
            } else {
                Response::success(['response' => $data], 'Favoritos listados exitosamente', 200);
            }
        } else {
            Response::notFound();
        }
        break;
    default:
        Response::error('Método no permitido', 405);
        break;
}
