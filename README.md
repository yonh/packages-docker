# packages-docker
run  [packages 3.2.0](https://github.com/terramar-labs/packages) with docker



Dependency:` Docker` and `Docker-compose`



### update your domain in docker-compose.yml
for example:
```
...
    VIRTUAL_HOST: your.domain
    # if you don't use 443, you can ignore LETSENCRYPT_HOST
    LETSENCRYPT_HOST: your.domain
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
bin/console satis:build
chown application.application . -R
```



#### redeploy

```shell
docker-compose down
docker-compose up -d
docker exec -it packages bash
bin/console resque:worker:start
chown application.application . -R
```