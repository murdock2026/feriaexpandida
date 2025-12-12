# Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Copiamos el archivo index.php al directorio raíz de Apache
COPY index.php /var/www/html/

# Exponemos el puerto 80 (Apache ya arranca por defecto)
EXPOSE 80
