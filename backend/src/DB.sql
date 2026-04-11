CREATE DATABASE IF NOT EXISTS Jobify CHARACTER
SET
  utf8mb4 COLLATE utf8mb4_unicode_ci;

USE Jobify;

CREATE TABLE
  IF NOT EXISTS Usuario (
    idUsuario int auto_increment primary key,
    nombre varchar(32) not null,
    apellido varchar(32) not null,
    email varchar(64) not null unique,
    contraseña varchar(512) not null,
    fechaNac date not null,
    imagen text not null
  );

CREATE TABLE
  IF NOT EXISTS Usuario_Telefono (
    idUsuario int,
    telefono varchar(15),
    FOREIGN KEY (idUsuario) REFERENCES Usuario (idUsuario) ON DELETE CASCADE,
    PRIMARY KEY (idUsuario, telefono)
  );

CREATE TABLE
  IF NOT EXISTS Moderador (
    idModerador int primary key,
    fechaContratacion date,
    FOREIGN KEY (idModerador) REFERENCES Usuario (idUsuario) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Administrador (
    idAdministrador int primary key,
    fechaContratacion date,
    FOREIGN KEY (idAdministrador) REFERENCES Usuario (idUsuario) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Proveedor (
    idProveedor int primary key,
    experiencia int not null,
    descripcion varchar(300) not null,
    nombreEmpresa varchar(30) not null,
    accesToken varchar(256),
    refreshToken varchar(256),
    publicKey varchar(256),
    FOREIGN KEY (idProveedor) REFERENCES Usuario (idUsuario) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Proveedor_Titulo (
    idProveedor int,
    titulo varchar(40),
    PRIMARY KEY (idProveedor, titulo),
    FOREIGN KEY (idProveedor) REFERENCES Proveedor (idProveedor) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Proveedor_Habilidad (
    idProveedor int,
    habilidad varchar(40),
    PRIMARY KEY (idProveedor, habilidad),
    FOREIGN KEY (idProveedor) REFERENCES Proveedor (idProveedor) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Cliente (
    idCliente int primary key,
    fechaRegistro date default (current_date()),
    FOREIGN KEY (idCliente) REFERENCES Usuario (idUsuario) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Servicio (
    idServicio int auto_increment primary key,
    idProveedor int,
    titulo varchar(30),
    descripcion varchar(300),
    precio int,
    FOREIGN KEY (idProveedor) REFERENCES Proveedor (idProveedor) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Cliente_Favorito (
    idCliente int,
    servicioFavorito int,
    primary key (idCliente, servicioFavorito),
    foreign key (idCliente) references Cliente (idCliente) ON DELETE CASCADE,
    foreign key (servicioFavorito) references Servicio (idServicio) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Servicio_Categoria (
    idServicio int,
    categoria varchar(20),
    PRIMARY KEY (idServicio, categoria),
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Servicio_Ubicacion (
    idServicio int,
    ubicacion enum (
      'Montevideo',
      'Canelones',
      'Maldonado',
      'San José',
      'Colonia',
      'Lavalleja',
      'Rocha',
      'Tacuarembó',
      'Paysandú',
      'Salto',
      'Rivera',
      'Artigas',
      'Cerro Largo',
      'Durazno',
      'Florida',
      'Soriano',
      'Treinta y Tres',
      'Flores',
      'Rio Negro'
    ),
    PRIMARY KEY (idServicio, ubicacion),
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Servicio_DiaDisponible (
    idServicio int,
    diaDisponible enum (
      'Lunes',
      'Martes',
      'Miercoles',
      'Jueves',
      'Viernes',
      'Sabado',
      'Domingo'
    ),
    PRIMARY KEY (idServicio, diaDisponible),
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Servicio_Imagen (
    idServicio int,
    imagen varchar(500),
    PRIMARY KEY (idServicio, imagen),
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Contrata (
    idReserva int auto_increment,
    idCliente int,
    idServicio int,
    fecha date,
    confirmado boolean,
    PRIMARY KEY (idReserva, idCliente, idServicio),
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE,
    FOREIGN KEY (idCliente) REFERENCES Cliente (idCliente) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Contacta (
    idCliente int,
    idServicio int,
    idProveedor int,
    PRIMARY KEY (idCliente, idServicio),
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE,
    FOREIGN KEY (idCliente) REFERENCES Cliente (idCliente) ON DELETE CASCADE,
    FOREIGN KEY (idProveedor) REFERENCES Proveedor (idProveedor) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Contacta_Mensaje (
    idMensaje INT NOT NULL AUTO_INCREMENT,
    idCliente INT NOT NULL,
    idServicio INT NOT NULL,
    idEmisor INT NOT NULL,
    mensaje VARCHAR(300) NOT NULL,
    PRIMARY KEY (idMensaje, idCliente, idServicio),
    FOREIGN KEY (idEmisor) REFERENCES Usuario (idUsuario) ON DELETE CASCADE,
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE,
    FOREIGN KEY (idCliente) REFERENCES Cliente (idCliente) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Resena (
    idResena int auto_increment primary key,
    calificacion int,
    comentario varchar(300) not null,
    imagen varchar(500)
  );

CREATE TABLE
  IF NOT EXISTS Modera (
    idModerador int,
    idResena int,
    PRIMARY KEY (idModerador, idResena),
    FOREIGN KEY (idModerador) REFERENCES Moderador (idModerador) ON DELETE CASCADE,
    FOREIGN KEY (idResena) REFERENCES Resena (idResena) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Publica (
    idCliente int,
    idServicio int,
    idResena int,
    PRIMARY KEY (idCliente, idServicio),
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE,
    FOREIGN KEY (idCliente) REFERENCES Cliente (idCliente) ON DELETE CASCADE,
    FOREIGN KEY (idResena) REFERENCES Resena (idResena) ON DELETE CASCADE
  );

CREATE TABLE
  IF NOT EXISTS Pagos (
    idCliente int,
    idServicio int,
    idProveedor int,
    fechaPago datetime,
    estado enum ('pendiente', 'completado', 'fallido'),
    precio decimal(10, 2),
    titulo varchar(100),
    paymentId varchar(100),
    PRIMARY KEY (idCliente, idServicio, fechaPago),
    FOREIGN KEY (idCliente) REFERENCES Cliente (idCliente) ON DELETE CASCADE,
    FOREIGN KEY (idServicio) REFERENCES Servicio (idServicio) ON DELETE CASCADE,
    FOREIGN KEY (idProveedor) REFERENCES Proveedor (idProveedor) ON DELETE CASCADE
  );