php bin/vendors install
Création db et mv app/config/parameters.ini.dist app/config/parameters.ini
Vérif mod_rewrite apache2 actif.
php app/console doctrine:schema:create
php app/console fos:user:create