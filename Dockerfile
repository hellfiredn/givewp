FROM wordpress:6.2.2

COPY ./src/noithatdiemnhancom/.htaccess /var/www/html/.htaccess
COPY ./src/noithatdiemnhancom/wp-content/themes /var/www/html/wp-content/themes
COPY ./src/noithatdiemnhancom/wp-content/plugins /var/www/html/wp-content/plugins

# COPY ./healthcheck-pipeline.sh /src/healthcheck-pipeline.sh

RUN usermod -u 1000 www-data &&\
  chown -R www-data:www-data /var/www/html &&\
  chmod +x /src/healthcheck-pipeline.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s CMD curl --fail http://localhost || exit 1
