FROM php:8.2-apache

# Bật mod_rewrite của Apache (rất cần thiết cho nhiều Framework PHP)
RUN a2enmod rewrite

# Cài đặt extension pdo_mysql để PHP kết nối được với MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql

# Cấp quyền cho thư mục web (để tránh lỗi permission denied)
RUN chown -R www-data:www-data /var/www/html
