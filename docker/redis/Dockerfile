# docker/redis/Dockerfile

# REDIS 5
FROM redis:5-alpine

# Работа от пользователя
ARG USER_UID
ARG USER_GID
RUN apk add --no-cache shadow
RUN usermod -u ${USER_UID} redis && groupmod -g ${USER_GID} redis

CMD ["redis-server", "/etc/redis.conf"]
EXPOSE 6379
