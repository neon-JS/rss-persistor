FROM php:8.2-cli-alpine AS base
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html/
COPY src/ .

FROM base AS setup
WORKDIR /app

RUN apk update && apk upgrade
RUN apk add curl

# Add cron
RUN touch persist-rss
RUN echo "0 */12 * * * /usr/local/bin/php /var/www/html/neonjs/rsspersistor/index.php >> /var/log/cron.log 2>&1" >>  persist-rss

RUN chmod 0644 persist-rss
RUN crontab persist-rss
RUN touch /var/log/cron.log

# Start cron
CMD env >> /etc/environment && crond && tail -f /var/log/cron.log