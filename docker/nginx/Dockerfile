# docker/nginx/Dockerfile

# Nginx 1.19
FROM nginx:1.19-alpine

# Работа от пользователя
ARG USER_UID
ARG USER_GID
RUN apk add --no-cache shadow
RUN usermod -u ${USER_UID} nginx && groupmod -g ${USER_GID} nginx

# Рабочая директория
WORKDIR /var/www/

# Запуск
CMD ["nginx"]
EXPOSE 80
