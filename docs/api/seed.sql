-- Minimal deterministic dataset for exercising the Jobify legacy API.
-- Load with: mariadb -h127.0.0.1 -P3307 -ujobify -p Jobify < docs/api/seed.sql
-- IDs are fixed so golden cases stay stable.
USE Jobify;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM Contacta_Mensaje;
DELETE FROM Contacta;
DELETE FROM Contrata;
DELETE FROM Cliente_Favorito;
DELETE FROM Publica;
DELETE FROM Resena;
DELETE FROM Pagos;
DELETE FROM Servicio_Imagen;
DELETE FROM Servicio_DiaDisponible;
DELETE FROM Servicio_Ubicacion;
DELETE FROM Servicio_Categoria;
DELETE FROM Servicio;
DELETE FROM Proveedor_Titulo;
DELETE FROM Proveedor_Habilidad;
DELETE FROM Proveedor;
DELETE FROM Moderador;
DELETE FROM Administrador;
DELETE FROM Cliente;
DELETE FROM Usuario_Telefono;
DELETE FROM Usuario;
SET FOREIGN_KEY_CHECKS = 1;

-- id 1: cliente / id 2: proveedor / id 3: moderador
INSERT INTO Usuario (idUsuario, nombre, apellido, email, contraseña, fechaNac, imagen) VALUES
  (1, 'Juan', 'Perez', 'juan@example.com', '$2y$12$cnUUibL1IVzBHq.YNB0BuO456LKLXe6tSG7ZcBW5ZNI5dBIrllTae', '1990-01-01', 'https://example.com/avatars/juan.png'),
  (2, 'Maria', 'Gomez', 'maria@example.com', '$2y$12$h3YZ73FeyDokYxIHqCe6j.k2VYQ5.hxZoRjfCtcpnzzZKigik.Ari', '1985-05-05', 'https://example.com/avatars/maria.png'),
  (3, 'Pedro', 'Moderador', 'pedro@example.com', '$2y$12$Ccy7kbR01uVNkHl4/kZd8eSOTT1UKgSsjmOBZ7P485qJ7BquSEWk.', '1980-01-01', 'https://example.com/avatars/pedro.png');

INSERT INTO Usuario_Telefono (idUsuario, telefono) VALUES
  (1, '099111111'),
  (2, '099222222'),
  (3, '099333333');

INSERT INTO Cliente (idCliente, fechaRegistro) VALUES (1, '2026-01-10');
INSERT INTO Proveedor (idProveedor, experiencia, descripcion, nombreEmpresa, accesToken, refreshToken, publicKey) VALUES
  (2, 5, 'Plomeria y reparaciones generales', 'Gomez Servicios', NULL, NULL, NULL);
INSERT INTO Moderador (idModerador, fechaContratacion) VALUES (3, '2026-01-15');

INSERT INTO Servicio (idServicio, idProveedor, titulo, descripcion, precio) VALUES
  (1, 2, 'Plomeria a domicilio', 'Reparacion de canerias y griferia', 1500),
  (2, 2, 'Jardineria basica', 'Corte de cesped y poda', 2000);
INSERT INTO Servicio_Categoria (idServicio, categoria) VALUES (1, 'Plomeria'), (2, 'Jardineria');
INSERT INTO Servicio_Ubicacion (idServicio, ubicacion) VALUES (1, 'Montevideo'), (2, 'Canelones');
INSERT INTO Servicio_DiaDisponible (idServicio, diaDisponible) VALUES (1, 'Lunes'), (1, 'Miercoles'), (2, 'Viernes');
INSERT INTO Servicio_Imagen (idServicio, imagen) VALUES (1, 'https://example.com/services/plomeria.jpg'), (2, 'https://example.com/services/jardineria.jpg');

INSERT INTO Resena (idResena, calificacion, comentario, imagen) VALUES
  (1, 5, 'Excelente trabajo', NULL);
INSERT INTO Publica (idCliente, idServicio, idResena) VALUES (1, 1, 1);

INSERT INTO Contrata (idReserva, idCliente, idServicio, fecha, confirmado) VALUES
  (1, 1, 1, '2026-11-01', 0);

INSERT INTO Contacta (idCliente, idServicio, idProveedor) VALUES (1, 1, 2);
INSERT INTO Contacta_Mensaje (idMensaje, idCliente, idServicio, idEmisor, mensaje) VALUES
  (1, 1, 1, 1, 'Hola, necesito una reparacion');

INSERT INTO Pagos (idCliente, idServicio, idProveedor, fechaPago, estado, precio, titulo, paymentId) VALUES
  (1, 1, 2, '2026-10-01 10:00:00', 'pendiente', 1500.00, 'Plomeria a domicilio', NULL),
  (1, 1, 2, '2026-10-02 10:00:00', 'pendiente', 1500.00, 'Plomeria a domicilio', NULL),
  (1, 1, 2, '2026-10-03 10:00:00', 'completado', 1500.00, 'Plomeria a domicilio', NULL),
  (1, 2, 2, '2026-10-04 10:00:00', 'pendiente', 2000.00, 'Jardineria basica', NULL);

INSERT INTO Cliente_Favorito (idCliente, servicioFavorito) VALUES (1, 1);

ALTER TABLE Usuario AUTO_INCREMENT = 4;
ALTER TABLE Servicio AUTO_INCREMENT = 3;
ALTER TABLE Resena AUTO_INCREMENT = 2;
ALTER TABLE Contrata AUTO_INCREMENT = 2;
ALTER TABLE Contacta_Mensaje AUTO_INCREMENT = 2;
