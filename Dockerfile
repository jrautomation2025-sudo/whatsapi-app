# Usa a imagem oficial do PHP com o servidor Apache embutido
FROM php:8.2-apache

# Habilita o módulo de reescrita de URL (mod_rewrite) do Apache
RUN a2enmod rewrite

# Instala as extensões do banco de dados (PDO MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Copia todos os arquivos do seu repositório para a pasta pública do servidor
COPY . /var/www/html/

# Ajusta as permissões para o Apache conseguir ler e executar tudo
RUN chown -R www-data:www-data /var/www/html
