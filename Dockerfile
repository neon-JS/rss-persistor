FROM php:8.2-cli
RUN docker-php-ext-install pdo pdo_mysql

# see: https://stackoverflow.com/a/37458519
RUN apt update && apt upgrade -y
RUN apt install -y curl cron

# Add cron
RUN touch /etc/cron.d/persist-rss
RUN echo "0 */12 * * * /usr/local/bin/php /var/www/html/neonJs/rssPersistor/index.php >> /var/log/cron.log 2>&1" >>  /etc/cron.d/persist-rss

RUN chmod 0644 /etc/cron.d/persist-rss
RUN crontab /etc/cron.d/persist-rss
RUN touch /var/log/cron.log

# Start cron
CMD env >> /etc/environment && cron && tail -f /var/log/cron.log