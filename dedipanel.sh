#!/bin/bash

if [ `id -u` -ne 0 ]; then
	echo "Vous devez lancer ce script avec les droits root."
	exit 1;
fi

# Cette fonction vérifie si le packet passé en argument est installé
verify_packet () {
	# Vérifie que tous les packets nécessaires sont installés
	if [ `dpkg-query -W --showformat='${Status}\n' $1 | grep 'install ok installed' | wc -l` -ge 1 ]; then
		echo 1
	else
		echo 0
	fi
}

# Cette fonction affiche le message d'utilisation du script bash
# et quitte le script
usage () {
    echo "Usage: $0 [install dir|update dir|verify] [-v]"
    exit 1
}

case "$1" in
    install)
        # Par défaut le retour des commandes utilisées dans le script est ignoré
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
        git checkout tags/b4.02

        # Copie du fichier de config et des htaccess
        cp app/config/parameters.yml.dist app/config/parameters.yml
        cp web/.htaccess.dist web/.htaccess
        cp .htaccess.dist .htaccess

        # Ajout de la config apache du panel
        echo -e "<Directory /var/www/$2>\n
                AllowOverride All\n
            </Directory>" > /etc/apache2/conf.d/dedipanel

        service apache2 restart

        # Téléchargement des dépendances
        curl -s https://getcomposer.org/installer | php
        php composer.phar install --optimize-autoloader --prefer-dist

        # Vidage du cache et installation des assets
        php app/console cache:clear --env=prod --no-warmup
        php app/console cache:clear --env=installer --no-warmup

        # Modif des droits et du propriétaire
        chmod 775 ./
        chown -R www-data:www-data ./

        echo "Il ne vous reste plus qu'à indiquer votre adresse IP personnelle dans le fichier $2/installer_whitelist.txt afin d'accéder à l'installateur en ligne (http://wiki.dedicated-panel.net/b4:install, section \"Finaliser l'installation\")." >&3

        exit ${?}
    ;;

    update)
        cd $2

        # On dl les derniers commits (sans merger)
        git fetch --all
        # Puis on remet automatiquement le depot local a jour
        git reset --hard origin/master

		# Mise à jour de composer et mise à jour des dépendances
		php composer.phar self-update
		php composer.phar update --optimize-autoloader --prefer-dist

		# Téléchargement des dépendances
		curl -s https://getcomposer.org/installer | php
		php composer.phar install --optimize-autoloader --prefer-dist

		# Vidage du cache et installation des assets
		php app/console cache:clear --env=prod --no-warmup
		php app/console cache:clear --env=installer --no-warmup
		php app/console assets:install --env=installer

		# Modif des droits et du propriétaire
        chmod 775 ./
		chown -R www-data:www-data ./

		echo "Il ne vous reste plus qu'à indiquer votre adresse IP personnelle dans le fichier $2/installer_whitelist.txt afin d'accéder à l'installateur en ligne."

        exit ${?}
    ;;

	verify)
		# Tableau contenant la liste des erreurs
		errors=()
		
		# Fais un apt-get update pour être sur que les éventuelles installations 
		# de paquets consécutives à ce verify fonctionne correctement
		apt-get update >/dev/null

		# Vérifie que tous les packets nécessaires sont installés
		packets=('git' 'sqlite' 'mysql-server' 'php5-sqlite' 'apache2' 'php5' 'php5-mysql' 'curl' 'php5-intl' 'php-apc' 'phpmyadmin')
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
