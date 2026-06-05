CREATE DATABASE IF NOT EXISTS novedades_economica 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
USE novedades_economica;
-- ============================================
-- 1. USUARIOS Y ROLES
-- ============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'vendedor', 'cliente') DEFAULT 'cliente',
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    creado_por INT DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB;

-- Admin por defecto: usuario=admin1, password=admin123
INSERT INTO usuarios (usuario, password, rol, nombre, email, activo) VALUES 
('admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrador', 'admin@novedades.com', 1);

-- ============================================
-- 2. CATEGORÍAS Y SUBCATEGORÍAS
-- ============================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    icono VARCHAR(10) NOT NULL DEFAULT '📦',
    tipo ENUM('hombre', 'mujer', 'ninos', 'accesorios', 'belleza') NOT NULL,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_tipo (tipo),
    INDEX idx_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE subcategorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL,
    icono VARCHAR(10) NOT NULL DEFAULT '📦',
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subcat (id_categoria, slug),
    INDEX idx_categoria (id_categoria)
) ENGINE=InnoDB;

-- Insertar categorías base
INSERT INTO categorias (nombre, slug, icono, tipo, orden) VALUES 
('Hombre', 'hombre', '👨', 'hombre', 1),
('Mujer', 'mujer', '👩', 'mujer', 2),
('Niños', 'ninos', '👶', 'ninos', 3),
('Accesorios', 'accesorios', '✨', 'accesorios', 4),
('Belleza', 'belleza', '💄', 'belleza', 5);

-- ============================================
-- 3. PRODUCTOS
-- ============================================
CREATE TABLE productos (
    id VARCHAR(20) PRIMARY KEY,
    id_subcategoria INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion_corta TEXT,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    precio_oferta DECIMAL(10,2) DEFAULT NULL,
    img_principal VARCHAR(500) NOT NULL,
    material VARCHAR(100),
    nuevo BOOLEAN DEFAULT FALSE,
    destacado BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_subcategoria) REFERENCES subcategorias(id) ON DELETE RESTRICT,
    INDEX idx_subcategoria (id_subcategoria),
    INDEX idx_activo (activo),
    INDEX idx_nuevo (nuevo),
    INDEX idx_precio (precio),
    FULLTEXT KEY idx_busqueda (nombre, descripcion_corta)
) ENGINE=InnoDB;

-- ============================================
-- 4. TALLAS Y COLORES - NORMALIZADO
-- ============================================
CREATE TABLE tallas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(10) UNIQUE NOT NULL,
    orden INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO tallas (nombre, orden) VALUES 
('U', 1), ('CH', 2), ('M', 3), ('G', 4), ('XG', 5), ('XXG', 6),
('28', 10), ('30', 11), ('32', 12), ('34', 13), ('36', 14), ('38', 15), ('40', 16);

CREATE TABLE colores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    codigo_hex VARCHAR(7) NOT NULL,
    UNIQUE KEY unique_color (nombre, codigo_hex)
) ENGINE=InnoDB;

INSERT INTO colores (nombre, codigo_hex) VALUES 
('Negro', '#000000'), ('Blanco', '#FFFFFF'), ('Azul', '#4A90E2'), 
('Gris', '#808080'), ('Celeste', '#87CEEB'), ('Beige', '#F5F5DC'),
('Verde Olivo', '#556B2F'), ('Vino', '#722F37'), ('Café', '#6F4E37'),
('Lila', '#C8A2C8'), ('Azul Clásico', '#1E3A5F');

-- ============================================
-- 5. INVENTARIO POR TALLA/COLOR - NORMALIZADO
-- ============================================
CREATE TABLE producto_variantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto VARCHAR(20) NOT NULL,
    id_talla INT DEFAULT NULL,
    id_color INT DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_talla) REFERENCES tallas(id) ON DELETE SET NULL,
    FOREIGN KEY (id_color) REFERENCES colores(id) ON DELETE SET NULL,
    UNIQUE KEY unique_variante (id_producto, id_talla, id_color),
    INDEX idx_producto (id_producto),
    INDEX idx_stock (stock)
) ENGINE=InnoDB;

-- ============================================
-- 6. IMÁGENES ADICIONALES DEL PRODUCTO
-- ============================================
CREATE TABLE producto_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto VARCHAR(20) NOT NULL,
    url VARCHAR(500) NOT NULL,
    orden INT DEFAULT 0,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_producto (id_producto)
) ENGINE=InnoDB;

-- ============================================
-- 7. RESERVAS/COMPRAS
-- ============================================
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_vendedor INT DEFAULT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'preparando', 'lista', 'entregada', 'cancelada') DEFAULT 'pendiente',
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta', 'pendiente') DEFAULT 'pendiente',
    notas TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (id_usuario),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB;

CREATE TABLE reserva_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    id_variante INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (id_reserva) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_variante) REFERENCES producto_variantes(id) ON DELETE RESTRICT,
    INDEX idx_reserva (id_reserva)
) ENGINE=InnoDB;

-- ============================================
-- 8. HISTORIAL DE CAMBIOS DE STOCK
-- ============================================
CREATE TABLE stock_movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variante INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo ENUM('entrada', 'salida', 'ajuste', 'venta', 'devolucion') NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    motivo VARCHAR(255),
    id_reserva INT DEFAULT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variante) REFERENCES producto_variantes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_reserva) REFERENCES reservas(id) ON DELETE SET NULL,
    INDEX idx_variante (id_variante),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB;

-- ============================================
-- 9. CONFIGURACIÓN DE TIENDA
-- ============================================
CREATE TABLE configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor TEXT,
    descripcion VARCHAR(255)
) ENGINE=InnoDB;

INSERT INTO configuracion VALUES 
('nombre_tienda', 'Novedades Económica', 'Nombre de la tienda'),
('telefono_tienda', '6121234567', 'Teléfono de contacto'),
('email_tienda', 'contacto@novedades.com', 'Email de contacto'),
('direccion_tienda', 'La Paz, BCS', 'Dirección física'),
('moneda', 'MXN', 'Moneda utilizada'),
('iva', '16', 'Porcentaje de IVA');

-- ============================================
-- VISTAS ÚTILES
-- ============================================
CREATE VIEW vista_productos_completa AS
SELECT 
    p.id,
    p.nombre,
    p.descripcion_corta,
    p.descripcion,
    p.precio,
    p.precio_oferta,
    p.img_principal,
    p.material,
    p.nuevo,
    p.activo,
    c.nombre AS categoria,
    c.slug AS categoria_slug,
    c.icono AS categoria_icono,
    s.nombre AS subcategoria,
    s.slug AS subcategoria_slug,
    s.icono AS subcategoria_icono,
    SUM(pv.stock) AS stock_total
FROM productos p
JOIN subcategorias s ON p.id_subcategoria = s.id
JOIN categorias c ON s.id_categoria = c.id
LEFT JOIN producto_variantes pv ON p.id = pv.id_producto
WHERE p.activo = 1
GROUP BY p.id;

CREATE VIEW vista_reservas_detalle AS
SELECT 
    r.id AS id_reserva,
    r.fecha,
    r.estado,
    r.total,
    u.nombre AS cliente,
    u.telefono,
    p.id AS id_producto,
    p.nombre AS producto,
    t.nombre AS talla,
    col.nombre AS color,
    rd.cantidad,
    rd.precio_unitario,
    rd.subtotal
FROM reservas r
JOIN usuarios u ON r.id_usuario = u.id
JOIN reserva_detalle rd ON r.id = rd.id_reserva
JOIN producto_variantes pv ON rd.id_variante = pv.id
JOIN productos p ON pv.id_producto = p.id
LEFT JOIN tallas t ON pv.id_talla = t.id
LEFT JOIN colores col ON pv.id_color = col.id;
select * from usuarios;
UPDATE usuarios SET 
    rol = 'admin',
    nombre = 'Administrador',
    activo = 1
WHERE usuario = 'admin1';
UPDATE usuarios SET password = '$2y$10$eImiTXuWVxfM37uY4JANjOLiL2v0wV9wVrH1YpIuM4Q1t6h4Z7Q8e' WHERE usuario = 'admin1';
CREATE TABLE codigos_reset (
    id_usuario INT PRIMARY KEY,
    codigo VARCHAR(6) NOT NULL,
    expira DATETIME NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);


CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2),
    estado VARCHAR(50) DEFAULT 'completado'
);

CREATE TABLE detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT,
    id_producto INT,
    cantidad INT,
    precio DECIMAL(10,2)
);
select*from tables;
show tables;
-- TABLA PEDIDOS - Esta es la que te marca error
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    direccion TEXT,
    telefono_contacto VARCHAR(20),
    metodo_pago VARCHAR(50),
    notas TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- TABLA DETALLE_PEDIDOS - Los productos de cada pedido
CREATE TABLE detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT
);
SHOW COLUMNS FROM productos WHERE Field = 'id';


-- BORRA LA TABLA SI YA LA CREASTE MAL
DROP TABLE IF EXISTS detalle_pedidos;
DROP TABLE IF EXISTS pedidos;

-- CREA PEDIDOS CORRECTAMENTE CON ID INT
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    direccion TEXT,
    telefono_contacto VARCHAR(20),
    metodo_pago VARCHAR(50),
    notas TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- CREA DETALLE_PEDIDOS
CREATE TABLE detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT
);
DESCRIBE productos;
CREATE TABLE detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto VARCHAR(20) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT
);