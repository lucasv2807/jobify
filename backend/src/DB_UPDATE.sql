-- Script para actualizar la base de datos existente con la nueva columna publicKey
-- Ejecutar solo si la base de datos ya existe y no tiene esta columna
USE Jobify;

-- Agregar columna publicKey a la tabla Proveedor si no existe
ALTER TABLE Proveedor
ADD COLUMN IF NOT EXISTS publicKey varchar(256);

-- Agregar columna paymentId a la tabla Pagos para guardar el ID de MercadoPago
ALTER TABLE Pagos
ADD COLUMN IF NOT EXISTS paymentId varchar(100);

-- Verificar que la tabla Pagos existe y tiene las columnas correctas
DESCRIBE Pagos;

-- Si necesitas ver los pagos existentes:
-- SELECT * FROM Pagos LIMIT 5;