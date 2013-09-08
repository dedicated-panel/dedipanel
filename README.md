dedipanel
=========

Panel permettant une gestion aisé de serveurs de jeux (Steam, COD, Minecraft ...) et de serveurs vocaux (Mumble, TS ...)

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


Auteur
-------

Dedicated-panel est un projet créé par Kerouanton Albin et Smedts Jérôme
