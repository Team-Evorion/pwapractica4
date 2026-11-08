# Terry Ponce — Frontend independiente (tema morado elegante)

## Contenido
- `ej02/index.php`: presentación del catálogo y modal. El bloque PHP de compra y consulta
  sigue el esquema que venía en el ZIP original.
- `ej02/style.css`: interfaz morada elegante, responsive, estados hover/focus, alertas,
  catálogo vacío y modal.
- `ej02/script.js`: modal de compra, validación existente y búsqueda en el catálogo.
- `ej02/img/productos/sin-imagen.svg`: imagen local de respaldo.
- `ej02/conexion.php`: archivo provisional de conexión para pruebas de estructura.

## Importante para la integración
Este paquete es una maqueta funcional de frontend, no una versión conectada a la base real.
Antes de integrarlo, reemplaza `ej02/conexion.php` por el archivo definitivo de José.
No reemplaces sus consultas ni el procesamiento de compras con este paquete si han cambiado.
Verifica también que la tabla y los campos sigan coincidiendo con los usados en `index.php`.

No se incluyen `login.php` ni `registro.php` porque no estaban presentes en el ZIP inicial.
Cuando estén disponibles, se podrán diseñar sin alterar sus nombres de campos ni su lógica.

## Rama Git
En el repositorio real del grupo:

    git switch -c terry-frontend

Copia los archivos visuales sobre `ej02/`, revisa los cambios y confirma solo tu trabajo:

    git status
    git add ej02/index.php ej02/style.css ej02/script.js ej02/img/productos/sin-imagen.svg
    git commit -m "Diseño frontend morado elegante para catálogo"

No se incluye una carpeta `.git`; la rama debe crearse en el repositorio original del equipo.
