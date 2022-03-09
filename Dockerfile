FROM php:8.1-cli

ARG UID=1000
ARG GID=1000

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
ADD https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp /usr/local/bin

RUN chmod +x /usr/local/bin/install-php-extensions

RUN apt-get update \
    && apt-get install -y git unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

RUN addgroup --gid $GID application \
    && adduser --uid $UID --gid $GID --disabled-password --home /home/application application

USER application

COPY app /application
WORKDIR /application
