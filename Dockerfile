FROM yiisoftware/yii2-php:7.2-apache

RUN rm -rf /var/www/html && ln -s /app/ /var/www/html || true