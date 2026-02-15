FROM trafex/php-nginx:latest

WORKDIR /var/www/html

USER root

COPY index.php /var/www/html/
COPY webhook.php /var/www/html/
COPY api.php /var/www/html/
COPY assets/ /var/www/html/assets/

RUN touch /var/www/html/mensagens.json \
    && touch /var/www/html/webhook_log.json \
    && touch /var/www/html/config.json \
    && echo '{"evolution_url":"https://evo.realizador.com.br","evolution_apikey":"20E12095-CE9F-4F16-9FB5-9DD63690E1B7","evolution_instance":"Controle_11"}' > /var/www/html/config.json \
    && chmod 666 /var/www/html/mensagens.json \
    && chmod 666 /var/www/html/webhook_log.json \
    && chmod 666 /var/www/html/config.json \
    && chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html

USER nginx

EXPOSE 8080
