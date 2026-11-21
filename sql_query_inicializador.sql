CREATE DATABASE IF NOT EXISTS caso_1_sistema_de_inventario
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
USE caso_1_sistema_de_inventario;


CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL UNIQUE
);

INSERT IGNORE INTO categorias (nombre) VALUES
    ('Accesorios'),
    ('Almacenamiento'),
    ('Conectividad');


CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    precio DECIMAL(10,2) NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 0,
    fecha_registro TIMESTAMP
    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_categoria INT NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    
    CONSTRAINT fk_producto_categoria
    	FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);


CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    estado BOOLEAN DEFAULT TRUE -- se asume que el estado del usuario es true (activo)
);


CREATE TABLE IF NOT EXISTS tipo_movimiento (
    id_tipo_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255)
);

INSERT IGNORE INTO tipo_movimiento (nombre, descripcion) VALUES
	('Entrada', 'Este producto entro en el inventario (Restock de productos, devoluciones, etc.)'),
    ('Salida', 'Este producto salio del inventario (Venta, producto en mal estado, etc)');


CREATE TABLE IF NOT EXISTS movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_tipo_movimiento INT NOT NULL, -- apunta a otra tabla donde puede ser entrada o salida
    cantidad INT NOT NULL,
    observacion VARCHAR(255),
    id_producto INT NOT NULL, -- apunta a el producto comprado
    id_usuario INT NOT NULL, -- apunta a el usuario que realizo el movimiento
    
    CONSTRAINT fk_tipo_movimiento
    	FOREIGN KEY (id_tipo_movimiento) REFERENCES tipo_movimiento(id_tipo_movimiento),
    
    CONSTRAINT fk_producto_movimiento
    	FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    
    CONSTRAINT fk_usuario_movimiento
    	FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);