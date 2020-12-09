#/**
# * TangoMan symfony-nginx.dockerfile
# *
# * Symfony 5 nginx alpine Dockerfile
# *
# * @version  0.1.0
# * @author   "Matthias Morin" <mat@tangoman.io>
# * @license  MIT
# */

FROM nginx:1.19-alpine

# # Install HTTPS requirements
# RUN \
#     apk add --no-cache --virtual .build-deps \
#         openssl && \
#     mkdir -p /etc/nginx/ssl && \
#     openssl req -subj '/CN=localhost' -days 365 -x509 -newkey rsa:4096 -nodes \
#         -keyout /etc/nginx/ssl/server.key -out /etc/nginx/ssl/server.crt && \
#     apk del .build-deps

WORKDIR /www
