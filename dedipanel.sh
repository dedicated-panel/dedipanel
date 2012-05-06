#!/bin/bash

case "$1" in
    install)
        # Dl de la derniere maj
        git clone http://github.com/NiR-/dedipanel.git $2
        cd $2

        # Modif des infos concernant la bdd
        # Copie du htaccess, et modif des droits d'acces
        cp app/config/parameters.ini.dist app/config/parameters.ini
        vim app/config/parameters.ini
        cp web/.htaccess.dist web/.htaccess
        chown -R www-data:www-data ./
        chmod -R g+w ./

        # Installation des vendors et parametrage du panel
        php bin/vendors install
        php app/console doctrine:schema:create
        php app/console init:acl
        php app/console assets:install --symlink web/
        php app/console fos:user:create --super-admin

        exit ${?}
    ;;
	
    update)
        cd $2
        # On dl les derniers commits (sans merger)
        git fetch --all
        # Puis on remet automatiquement le depot local a jour
        git reset --hard origin/master
        
        chmod -R g+w app
        chown -R www-data:www-data ./
        php bin/vendors install
        php app/console doctrine:schema:update --force
        php app/console assets:install --symlink web/

        exit ${?}
    ;;
	
    *)
        echo "Usage: $0 install|update dir"
        exit ${?}
    ;;
esac