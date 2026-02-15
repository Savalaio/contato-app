FROM php:8.2-apache

WORKDIR /var/www/html

# Instalar extensões necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Copiar arquivos
COPY index.php /var/www/html/
COPY webhook.php /var/www/html/
COPY api.php /var/www/html/
COPY assets/ /var/www/html/assets/

# Criar config.json com credenciais
RUN echo '{"evolution_url":"https://evo.realizador.com.br","evolution_apikey":"20E12095-CE9F-4F16-9FB5-9DD63690E1B7","evolution_instance":"Controle_11"}' > /var/www/html/config.json

# Criar arquivos necessários
RUN touch /var/www/html/mensagens.json \
    && touch /var/www/html/webhook_log.json \
    && chmod 666 /var/www/html/mensagens.json \
    && chmod 666 /var/www/html/webhook_log.json \
    && chmod 666 /var/www/html/config.json \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
