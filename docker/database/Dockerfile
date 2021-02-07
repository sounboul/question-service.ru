# docker/database/Dockerfile

# PostgreSQL 11
FROM postgres:11-alpine

# Работа от пользователя
ARG USER_UID
ARG USER_GID
RUN apk add --no-cache shadow
RUN usermod -u ${USER_UID} postgres && groupmod -g ${USER_GID} postgres

# Запуск
CMD ["postgres"]
EXPOSE 5432
