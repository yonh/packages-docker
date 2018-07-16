# packages-docker
run `packages3.2.0` with docker

Dependency:` Docker` and `Docker-compose`





### update your domain in docker-compose.yml
for example:
```
...
    VIRTUAL_HOST: your.domain
    LETSENCRYPT_HOST: your.domain # if you don't use 443, you can ignore this line
...
```

```shell
# copy config.yml to app folder and update some settings you need
cp config.yml.dist app/config.yml

# run with docker-compose
docker-compose up -d

# get in the container
docker exec -it packages bash

# init app
cd /app
composer install --no-dev
bin/console orm:schema-tool:create
bin/console resque:worker:start
chown application.application . -R
```
