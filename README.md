# DediPanel
[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/dedicated-panel/dedipanel?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Build Status](https://travis-ci.org/dedicated-panel/dedipanel.svg?branch=b5)](https://travis-ci.org/dedicated-panel/dedipanel)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dedicated-panel/dedipanel/badges/quality-score.png?b=b5)](https://scrutinizer-ci.com/g/dedicated-panel/dedipanel/?branch=b5)

Panel d'administration de serveurs de jeux (Steam, Minecraft) et de serveurs vocaux (TeamSpeak3).

Communauté
----------

[Site](http://www.dedicated-panel.net).

[Forum](http://forum.dedicated-panel.net).

[Wiki](http://wiki.dedicated-panel.net).



Installation
------------

``` bash
$ cd /var/www/
$ wget -O dedipanel.sh https://raw.github.com/NiR-/dedipanel/master/dedipanel.sh && chmod +x dedipanel.sh
$ ./dedipanel.sh verify
$ ./dedipanel.sh install dedipanel
```


Si vous n'avez pas eu d'erreur à l'étape précédente, l'installation se terminera dans votre navigateur ;) 

Mais avant cela, il faudra modifier le fichier installer_whitelist.txt, présent à la racine du panel (/var/www/dedipanel, si vous avez laisser le répertoire indiqué ci-dessus). 

Vous devrez indiquer votre adresse IP (la votre, celle de votre freebox|neufbox|orangebox|...; pas celle de votre serveur !)\\

Rendez-vous donc à l'adresse http://votre.serveur/dedipanel/web/installer/. 

A partir de la, l'installation est simplifié en passant par l'adresse http://votre.machine/dedipanel/web/


Erreurs
-------

Si vous rencontrez le moindre problème, pensez a venir sur notre forum avec le 2 commandes si dessous pour avoir des log complet de l'erreur.


````
tail -f app/logs/prod.log
tail -f app/logs/dev.log
````

Droits d'accès
-------
Un système de groupe est présent. Il permet de créer des groupes et des sous-groupes, et d'assigner des droits spécifiques pour chaque groupe.
Il permet également d'assigner des machines à des groupes. Les serveurs de jeux et les serveurs VoIP sont ainsi assignés à un groupe par le biais de leur machine.

Tous les utilisateurs, sauf les super-admins doivent appartenir à un groupe.
Les super-admin ont en charge la gestion globale du panel. Ils peuvent modifier la configuration des jeux, des plugins ainsi que la configuration générale.

Des admins peuvent être définis pour chaque groupe.
Ils ont en charge la gestion des utilisateur, utilisateurs, des groupes et des machines du groupes et de ses sous-groupes.

Roles :
  * ROLE_DP_STEAM_* / ROLE_DP_MINECRAFT_* :
    * ROLE_DP_STEAM_ADMIN: Affichage des logs, régénération des scripts du panel. Hérité des autres rôles. 
    * ROLE_DP_STEAM_SHOW: Affichage de la liste des serveurs, affichage des détails d'un serveurs.
    * ROLE_DP_STEAM_ADD: Création de serveurs. Peux mettre à jour/relance une installation.
    * ROLE_DP_STEAM_EDIT: Modification des serveurs. Peux mettre à jour/relancer une installation.
    * ROLE_DP_STEAM_STATE: Peux démarrer/arrêter/redémarrer les serveurs.
    * ROLE_DP_STEAM_FTP: Accès à la partie ftp des serveurs.
    * ROLE_DP_STEAM_PLUGIN: Gestion des plugins des serveurs.
    * ROLE_DP_STEAM_RCON: Accès à la console rcon.
    * ROLE_DP_STEAM_HLTV: Gestion de la HLTV.
  * ROLE_DP_ADMIN_* :
    * ROLE_DP_ADMIN_USER_ADMIN: Gestion des utilisateurs
    * ROLE_DP_ADMIN_GROUP_ADMIN: Gestion des groupes
    * ROLE_DP_ADMIN_GAME_ADMIN: Gestion des jeux
    * ROLE_DP_ADMIN_PLUGIN_ADMIN: Gestion des plugins
    * ROLE_DP_ADMIN_MACHINE_ADMIN: Gestion des machines


### Testing
```
composer install --dev
php app/console doctrine:database:create --env=test
bin/behat
```

Auteur
-------

Dedicated-panel est un projet créé par Kerouanton Albin et Smedts Jérôme
