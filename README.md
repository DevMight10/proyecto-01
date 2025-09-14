# Mini Chic - Tienda de Ropa de Bebé

Sistema web para la venta de productos en línea de la tienda Mini Chic, especializada en ropa de bebé.

## Características

- Catálogo de productos con filtros por categoría
- Sistema de autenticación (registro/login)
- Carrito de compras para usuarios autenticados
- Panel de administración para gestión de productos y pedidos
- Sistema de contacto y mensajes
- Diseño responsive con temática de bebé

## Instalación

1. Descargar e instalar XAMPP
2. Copiar la carpeta del proyecto en `htdocs/mini_chic/`
3. Iniciar Apache y MySQL en XAMPP
4. Importar la base de datos desde `database.sql`
5. Acceder a `http://localhost/mini_chic/`

## Credenciales de Administrador

- Email: admin@minichic.com
- Password: password

## Estructura del Proyecto

- `/config/` - Configuración de base de datos y sesiones
- `/includes/` - Archivos comunes (header, footer, funciones)
- `/admin/` - Panel de administración
- `/assets/` - CSS, JavaScript e imágenes
- `/uploads/` - Imágenes de productos

## Tecnologías Utilizadas

- PHP 7.4+
- MySQL
- HTML5/CSS3
- JavaScript
- Font Awesome (iconos)

## Funcionalidades

### Para Clientes
- Ver catálogo de productos
- Filtrar por categorías
- Registrarse e iniciar sesión
- Agregar productos al carrito
- Confirmar pedidos
- Enviar mensajes de contacto

### Para Administradores
- Gestionar productos (CRUD)
- Ver y actualizar estados de pedidos
- Leer mensajes de contacto
- Dashboard con estadísticas

## Requerimientos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache Web Server
- Extensiones PHP: PDO, GD (para imágenes)

## Notas de Desarrollo

Este proyecto fue desarrollado como parte de un proyecto estudiantil para la tienda Mini Chic ubicada en Satélite Norte, La Paz - Bolivia.
