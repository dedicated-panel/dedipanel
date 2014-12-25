#!/bin/bash

# On récupère l'utilisateur et le groupe d'apache (s'ils sont déclarés), afin d'assigner le bon proprio lors du chown
# On s'assure également que COMPOSER_HOME soit déclaré puisque HOME est écrasé par l'appel à /etc/apache2/envvars
USER='www-data'
GROUP='www-data'
export COMPOSER_HOME=$HOME/.composer
[ -f /etc/apache2/envvars ] && . /etc/apache2/envvars
[ -n "${APACHE_RUN_USER}" ] && USER="${APACHE_RUN_USER}"
[ -n "${APACHE_RUN_GROUP}" ] && GROUP="${APACHE_RUN_GROUP}"

# Vérifie si le packet passé en argument est installé
verify_packet () {
	if [ `dpkg-query -W --showformat='${Status}\n' $1 2>/dev/null | grep 'install ok installed' | wc -l` -ge 1 ]; then
		echo 1
	else
		echo 0
	fi
}

# Cette fonction affiche le message d'utilisation du script bash et quitte le script
usage () {
    echo "Usage: $0 [install dir|update dir|verify] [-v]"
    exit 1
}

# Copie les fichiers de config .yml.dist et les fichiers .htaccess
copy_dists_file () {
    [ ! -f app/config/parameters.yml ] && cp app/config/parameters.yml.dist app/config/parameters.yml
    [ ! -f app/config/dedipanel.yml ] && cp app/config/dedipanel.yml.dist app/config/dedipanel.yml
    [ ! -f web/.htaccess ] && cp web/.htaccess.dist web/.htaccess
    [ ! -f .htaccess ] && cp .htaccess.dist .htaccess
}

# Création du cache (suppression préalable du cache existant)
clear_cache () {
    [ -d app/cache/ ] && rm -rf app/cache/

    php app/console cache:clear --env=prod
    php app/console cache:clear --env=installer
    php app/console assets:install --env=prod web
}

# Installation des dépendences externes
install_vendor () {
    [ ! -f composer.phar ] && curl -s https://getcomposer.org/installer | php

    if [ -d vendor/ ]; then
        php composer.phar update --optimize-autoloader --prefer-dist --no-dev
    else
        php composer.phar install --optimize-autoloader --prefer-dist --no-dev
    fi
}

# Configuration d'apache2 (sauf si le fichier de config existe déjà ou si la config préexistante est buggué)
configure_apache () {
    if [ "`apache2ctl -t 2>/dev/null && echo $?`" = "1" ]; then
        echo "Vous avez besoin de reparer votre configuration apache2 avant de continuer l'intallation. Pour en savoir plus, executez cette commande : apache2ctl -t"
        exit 1
    fi

    if [ -d /etc/apache2/conf-available ]; then
        DIR='/etc/apache2/conf-available/'
    elif [ -d /etc/apache2/conf.d ]; then
        DIR='/etc/apache2/conf.d'
    else
        exit 1
    fi

    if [ -f $DIR/dedipanel ]; then
        exit 0
    fi

    cat << EOF > $DIR/dedipanel
<Directory /var/www/$2>
    AllowOverride All
</Directory>
EOF

    service apache2 reload
}

case "$1" in
    install)
        DEBUG=0

        # 0n vérifie qu'il n'y ai pas d'erreur sur la position du flag de debug
        # et que le nom du dossier d'installation est fourni
        if [ $# -eq 3 -a "$3" = "-v" ]; then
            DEBUG=1
        elif [ $# -eq 1 -o "$1" = "-v" -o "$2" = "-v" ]; then
            usage
        fi
        
        # Désactive stdout et stderr si le mode debug n'est pas activé
        # et créer un file descriptor qui sera utilisé pour afficher les messages destinés à l'utilisateur
        if [ $DEBUG -eq 0 ]; then
            exec 3>&1 &>/dev/null
        else
            exec 3>&1
        fi
        
        $0 verify 1>/dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo "Merci d'effectuer les opérations préalablement nécessaire à l'installation du panel (utilisez la commande \"$0 verify\" pour vérifier la configuration de votre serveur)." >&3
            exit 1
        fi
        
        # Dl de la dernière maj du panel
        git clone http://github.com/NiR-/dedipanel.git "$2"
        cd "$2"
        git checkout b5

        # Copie des fichiers de config et des htaccess
        copy_dists_file
        install_vendor
        configure_apache
        clear_cache

        # Modif des droits et du propriétaire
        chmod 775 ./
        chown -R $USER:$GROUP ./

        echo "Il ne vous reste plus qu'à indiquer votre adresse IP personnelle dans le fichier $2/installer_whitelist.txt afin d'accéder à l'installateur en ligne (http://wiki.dedicated-panel.net/b4:install, section \"Finaliser l'installation\")." >&3
    ;;

    update)
        $0 verify 1>/dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo "Merci d'effectuer les opérations préalablement nécessaire à la mise à jour du panel (utilisez la commande \"$0 verify\" pour vérifier la configuration de votre serveur)."
            exit 1
        fi

        cd $2

        # On dl les derniers commits (sans merger)
        # Puis on remet automatiquement le depot local a jour
        git fetch --all
        git reset --hard origin/b5

        # On s'assure que tous les fichiers .dist soient copiés
        copy_dists_file
        install_vendor
        configure_apache
		clear_cache

		# Modif des droits et du propriétaire
        chmod 775 ./
		chown -R $USER:$GROUP ./

		echo "Il ne vous reste plus qu'à indiquer votre adresse IP personnelle dans le fichier $2/installer_whitelist.txt afin d'accéder à l'installateur en ligne."
    ;;

	verify)
		# Tableau contenant la liste des erreurs
		errors=()

		# Vérifie que tous les packets nécessaires sont installés
		packets=('git' 'mysql-server' 'apache2' 'php5' 'php5-mysql' 'curl' 'php5-intl' 'php-apc')
		failed=()

		for packet in "${packets[@]}"; do
			if [ $(verify_packet $packet) -eq 0 ]; then
				failed=("${failed[@]}" $packet)

				if [[ ! ${errors[*]} =~ "packet" ]]; then
					errors=("${errors[@]}" "packet")
				fi
			fi
		done
		
		if [ ${#failed[@]} -ge 1 ]; then
			echo "Packets nécessaires: ${packets[@]}."
			echo "Packets manquants: ${failed[@]}."
		fi

		# Vérifie que le mode rewrite d'apache est activé
		if [ ! -e /etc/apache2/mods-enabled/rewrite.load ]; then
			errors=("${errors[@]}" "mod_rewrite")
			echo "Le mode rewrite d'apache doit être activé (a2enmod rewrite && service apache2 restart)."
		fi

		# Vérifie la présence de suhosin.executor.include.whitelist dans la config de php
		if [ -z "`sed -ne '/^suhosin.executor.include.whitelist/p' /etc/php5/cli/php.ini`" ]; then
			errors=("${errors[@]}" "suhosin_phar")
			echo "Vous devez ajouter la ligne suivante au fichier /etc/php5/cli/php.ini : suhosin.executor.include.whitelist = phar"
		fi

        # Vérifie que la config apache2 n'a pas de souci
        if [ "`apache2ctl -t 2>/dev/null && echo $?`" = "1" ]; then
            errors=("${errors[@]}" "bad_apache2_config")
            echo "Vous avez besoin de reparer votre configuration apache2. Pour en savoir plus, executez cette commande : apache2ctl -t"
        fi

		# Vérifie s'il y a eu des erreurs d'enregistrées
		if [ ${#errors[@]} -ge 1 ]; then
            echo ""
			echo "Veuillez effectuer les opérations préalablement nécessaire à l'installation du panel."
            exit 1
		else
			echo "Votre serveur est correctement configuré. Vous pouvez y installer le panel."
            exit 0
		fi
	;;

    *)
        usage
    ;;
esac
