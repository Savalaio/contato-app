FROM trafex/php-nginx:latest

WORKDIR /var/www/html

COPY app/ /var/www/html/

RUN chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html

