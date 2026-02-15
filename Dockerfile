FROM trafex/php-nginx:latest

USER root

WORKDIR /var/www/html

COPY index.php /var/www/html/
COPY webhook.php /var/www/html/
COPY api.php /var/www/html/
COPY config.json /var/www/html/
COPY assets/ /var/www/html/assets/

RUN touch /var/www/html/mensagens.json \
    && touch /var/www/html/webhook_log.json \
    && chmod 666 /var/www/html/mensagens.json \
    && chmod 666 /var/www/html/webhook_log.json \
    && chmod 666 /var/www/html/config.json \
    && chown -R nginx:nginx /var/www/html

USER nginx

EXPOSE 8080
