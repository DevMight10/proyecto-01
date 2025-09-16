USE mini_chic_db;

START TRANSACTION;

-- MISMA IMAGEN PARA TODOS
SET @img := 'prod_68c9eca302bc0.jpeg
';

INSERT INTO productos
  (nombre, descripcion, precio, categoria_id, imagen, stock, activo, destacado)
VALUES
-- 1) Recién Nacido (id 1)
('Body Algodón RN', 'Body 100% algodón orgánico, suave y respirable.', 79.90, 1, @img, 30, 1, 1),
('Pijama RN', 'Pijama manga larga con broches rápidos.', 99.00, 1, @img, 22, 1, 0),
('Manta RN', 'Manta cálida y ligera para recién nacido.', 120.00, 1, @img, 15, 1, 1),
('Guantes RN', 'Guantes suaves antiarañazo.', 29.50, 1, @img, 40, 1, 0),
('Gorro RN', 'Gorro térmico para mantener el calor.', 39.90, 1, @img, 35, 1, 0),
('Body Cruzado RN', 'Body cruzado con broches laterales.', 84.90, 1, @img, 28, 1, 0),
('Pantaloncito RN', 'Pantalón de algodón con pretina suave.', 49.00, 1, @img, 26, 1, 0),
('Enterizo RN', 'Enterizo liviano para uso diario.', 109.00, 1, @img, 18, 1, 1),

-- 2) 3-6 Meses (id 2)
('Conjunto 3-6', 'Camiseta + pantalón en algodón suave.', 139.00, 2, @img, 25, 1, 1),
('Pantalón 3-6', 'Pretina elástica, cómodo.', 59.90, 2, @img, 35, 1, 0),
('Camiseta 3-6', 'Estampado con tintas no tóxicas.', 49.90, 2, @img, 50, 1, 0),
('Body Pack 3-6', 'Pack de 2 bodies de algodón.', 109.00, 2, @img, 22, 1, 0),
('Short 3-6', 'Short de algodón peinado.', 39.90, 2, @img, 30, 1, 0),
('Chaquetita 3-6', 'Capa ligera para viento.', 119.00, 2, @img, 16, 1, 1),

-- 3) 6-12 Meses (id 3)
('Pijama 6-12', 'Pijama abrigada para climas fríos.', 149.00, 3, @img, 18, 1, 1),
('Chaqueta 6-12', 'Chaqueta ligera con forro suave.', 169.00, 3, @img, 12, 1, 0),
('Enterizo 6-12', 'Enterizo cómodo para juego diario.', 109.00, 3, @img, 20, 1, 0),
('Conjunto 6-12', 'Polera + jogger.', 159.00, 3, @img, 14, 1, 0),
('Pantalón 6-12', 'Algodón con puño elástico.', 64.90, 3, @img, 28, 1, 0),
('Chaleco 6-12', 'Chaleco acolchado liviano.', 129.00, 3, @img, 10, 1, 1),

-- 4) 1-2 Años (id 4)
('Vestido 1-2', 'Vestido liviano con estampado floral.', 159.00, 4, @img, 14, 1, 1),
('Jeans 1-2', 'Jeans con cintura elástica.', 129.00, 4, @img, 20, 1, 0),
('Sudadera 1-2', 'Interior french terry.', 149.00, 4, @img, 16, 1, 0),
('Set Bodies 1-2', 'Pack x3 bodies algodón peinado.', 179.00, 4, @img, 10, 1, 1),
('Jogger 1-2', 'Jogger suave con cordón.', 89.00, 4, @img, 18, 1, 0),
('Camisa 1-2', 'Camisa de algodón liviano.', 119.00, 4, @img, 12, 1, 0),

-- 5) Accesorios (id 5)
('Babero Impermeable', 'Reverso impermeable, fácil limpieza.', 34.50, 5, @img, 80, 1, 1),
('Calcetines x3', 'Pack de 3 pares antideslizantes.', 29.90, 5, @img, 70, 1, 0),
('Zapatos Soft', 'Zapatitos blandos primeros pasos.', 89.00, 5, @img, 25, 1, 0),
('Cobija Muselina', 'Muselina respirable multiuso.', 75.00, 5, @img, 30, 1, 0),
('Gorro Punto', 'Gorro tejido punto fino.', 45.00, 5, @img, 40, 1, 0),
('Mordedor Silicona', 'Mordedor de silicona grado alimenticio.', 32.00, 5, @img, 60, 1, 0);

COMMIT;
