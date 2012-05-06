#!/bin/bash

case "$1" in
	install)
		# Dl de la derniere maj
		git clone http://github.com/NiR-/dedipanel.git
		cd $2

		# Modif des infos concernant la bdd
		cp app/config/parameters.ini.dist app/config/parameters.ini
		vim app/config/parameters.ini
		chown -R www-data:www-data ./
		chmod g+w ./

		# Installation des vendors et parametrage du panel
		php bin/vendors install
		php app/console doctrine:schema:create
		php app/console init:acl
		php app/console fos:user:create --super-admin
		
		exit ${?}
	;;
	
	update)
		cd $2
		git pull
		chmod g+w app/cache
		chown -R www-data:www-data ./
		php bin/vendors install
		php app/console doctrine:schema:update --force
		
		exit ${?}
	;;
	
	*)
		echo "Usage: $0 install|update dir"
		exit ${?}
	;;
esac