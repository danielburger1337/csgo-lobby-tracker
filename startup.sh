#!/bin/sh

# https://symfony.com/doc/current/setup/file_permissions.html#1-using-acl-on-a-system-that-supports-setfacl-linux-bsd
mkdir -p var/
setfacl -dR -m u:www-data:rwX -m u:$(whoami):rwX var/
setfacl -R -m u:www-data:rwX -m u:$(whoami):rwX var/


bin/console cache:clear --quiet

bin/console doctrine:database:create --no-interaction --quiet
bin/console doctrine:migrations:migrate --no-interaction --quiet


composer dump-env prod --quiet
rm -f .env.prod.local


/usr/sbin/apache2ctl -D FOREGROUND
