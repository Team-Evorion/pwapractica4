# 🛒 UBE Store

### Tienda tecnológica | Programación Web Avanzada

**UBE Store** es una aplicación web de comercio electrónico desarrollada como proyecto académico para la asignatura de **Programación Web Avanzada** de la Universidad Bolivariana del Ecuador (UBE).

El sistema presenta un catálogo dinámico de productos tecnológicos conectado a MySQL. Los visitantes pueden explorar los artículos y los usuarios registrados pueden iniciar sesión, administrar un carrito de compras y confirmar pedidos desde una interfaz moderna y adaptable a diferentes dispositivos.

El proyecto integra frontend, backend y base de datos, con énfasis en la organización del código, la experiencia de usuario y el registro seguro de la información.

## 🌐 Enlaces del proyecto

- **Repositorio:** [GitHub — Team-Evorion/pwapractica4](https://github.com/Team-Evorion/pwapractica4)
- **Sitio web:** `PENDIENTE DE PUBLICACIÓN — agregar aquí la URL del hosting`

> Cuando el proyecto sea publicado, reemplazar el texto pendiente por el enlace público solicitado para la entrega.

---

## ✨ Características principales

- **Catálogo dinámico:** productos obtenidos desde MySQL con imágenes, descripciones, precios y vendedores.
- **Búsqueda y filtros:** consulta de productos por nombre y clasificación por categorías.
- **Registro e inicio de sesión:** autenticación de usuarios con sesiones de PHP y contraseñas protegidas mediante hash.
- **Carrito de compras:** incorporación de productos, modificación de cantidades, eliminación individual y vaciado completo.
- **Confirmación de compra:** registro del cliente y de sus pedidos en la base de datos.
- **Control de acceso:** el catálogo puede visitarse sin iniciar sesión, pero se requiere una cuenta para comprar.
- **Validaciones y seguridad básica:** consultas preparadas con PDO, escape de contenido, tokens CSRF y regeneración de sesión.
- **Interacciones visuales:** avisos flotantes, animación al añadir productos y accesos rápidos durante la navegación.
- **Diseño responsive:** interfaz adaptable a computadoras, tablets y teléfonos.
- **Presentación institucional:** páginas informativas sobre la tienda y los integrantes del proyecto.

---

## 🛠️ Tecnologías utilizadas

| Tecnología | Aplicación |
|---|---|
| **HTML5** | Estructura semántica de las páginas |
| **CSS3** | Identidad visual, componentes y diseño responsive |
| **JavaScript** | Filtros, validaciones, animaciones e interacciones |
| **PHP 8** | Autenticación, sesiones, carrito y registro de pedidos |
| **PDO** | Conexión segura y consultas preparadas a MySQL |
| **MySQL** | Almacenamiento de usuarios, vendedores, productos, clientes y pedidos |
| **XAMPP / phpMyAdmin** | Entorno local y administración de la base de datos |
| **Git y GitHub** | Control de versiones y trabajo colaborativo |

---

## 🗄️ Modelo de datos

La base de datos se denomina `tienda_ube` y está compuesta por cinco tablas principales:

- `usuarios`: almacena nombres, correos, contraseñas cifradas y roles.
- `vendedores`: contiene la información de los proveedores del catálogo.
- `productos`: guarda nombre, descripción, precio, imagen y vendedor asociado.
- `clientes`: registra los datos utilizados al confirmar una compra.
- `pedidos`: relaciona clientes y productos, incluyendo cantidad y fecha.

### Relaciones

- Un vendedor puede tener varios productos.
- Un cliente puede realizar varios pedidos.
- Cada pedido referencia un producto y conserva la cantidad solicitada.

---

## 🏗️ Estructura del proyecto

```text
pwapractica4/
├── database/
│   └── tienda_ube.sql       # Estructura y datos iniciales de MySQL
├── img/
│   └── productos/           # Imágenes del catálogo
├── index.php                # Página de inicio y productos destacados
├── productos.php            # Catálogo completo, búsqueda y filtros
├── carrito.php              # Carrito y confirmación de compra
├── login.php                # Inicio de sesión
├── registro.php             # Creación de cuentas
├── logout.php               # Cierre seguro de sesión
├── nosotros.php             # Información sobre UBE Store
├── equipo.php               # Integrantes del proyecto
├── conexion.php             # Conexión PDO con MySQL
├── js/script.js                # Interacciones del frontend
├── css/style.css                # Estilos generales y responsive
└── README.md                # Documentación del proyecto
```

---

## 🚀 Instalación y ejecución local

### Requisitos

- XAMPP con Apache y MySQL activos.
- PHP 8 o una versión compatible.
- Navegador web moderno.

### Pasos

1. Clonar o descargar este repositorio.
2. Colocar la carpeta `pwapractica4` dentro de `C:\xampp\htdocs\`.
3. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
4. Abrir [phpMyAdmin](http://localhost/phpmyadmin/).
5. Importar el archivo `database/tienda_ube.sql`.
6. Verificar en `conexion.php` los datos de conexión predeterminados:

```php
$host = 'localhost';
$dbname = 'tienda_ube';
$username = 'root';
$password = '';
```

7. Abrir la aplicación en:

```text
http://localhost/pwapractica4/
```

---

## 🔑 Acceso de prueba

El visitante puede crear una cuenta desde la página de registro para probar el inicio de sesión, el carrito y la confirmación de pedidos.

Por seguridad, las credenciales de demostración y de administración no se publican en este repositorio. Si son necesarias para una revisión académica, deben compartirse de manera privada con el docente.

---

## 🧪 Flujo recomendado de prueba

1. Explorar la página de inicio y abrir el catálogo completo.
2. Buscar productos y probar los filtros disponibles.
3. Crear una cuenta nueva o utilizar una cuenta de demostración.
4. Añadir varios productos al carrito.
5. Actualizar cantidades, eliminar un artículo y comprobar el total.
6. Escribir una dirección de entrega y confirmar la compra.
7. Verificar en phpMyAdmin los registros creados en `clientes` y `pedidos`.

---

## 👥 Equipo de desarrollo

Proyecto desarrollado por estudiantes de la **Universidad Bolivariana del Ecuador (UBE)**:

- Jesús Rafael Fernández Pérez
- José Andrés Merlin Gamboa
- José Alberto Rosado Dominguez
- Terry Steven Ponce Farías
- Elkin Daniel Velastegui Zambrano
- Zahid Joel Zambrano Moreira

---

## 📚 Contexto académico

**Institución:** Universidad Bolivariana del Ecuador (UBE)  
**Asignatura:** Programación Web Avanzada  
**Actividad:** Clase Práctica 4  
**Año:** 2026

UBE Store aplica conocimientos de desarrollo web del lado del cliente y del servidor mediante una solución integrada con HTML, CSS, JavaScript, PHP y MySQL.

---

## 📌 Alcance del proyecto

UBE Store fue desarrollado con fines educativos. El sistema permite simular compras y registrar pedidos, pero **no procesa pagos reales, tarjetas bancarias ni transacciones comerciales**.

---

### 🛒 UBE Store

**Tecnología para tu día a día.**
