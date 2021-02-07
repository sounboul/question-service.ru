# docker/elasticsearch/Dockerfile

# ELASTICSEARCH 7.10
FROM elasticsearch:7.10.1

# Работа от пользователя
ARG USER_UID
ARG USER_GID
RUN usermod -u ${USER_UID} elasticsearch && groupmod -g ${USER_GID} elasticsearch

CMD ["elasticsearch"]
EXPOSE 9200
