#!/bin/bash

if [ $# -eq 1 ]; then
    echo `pwd`
    JEU="$1"

	# On commence par télécharger le hldsupdatetool.bin
	wget http://storefront.steampowered.com/download/hldsupdatetool.bin
	
	# On lui donne les droits et on l'exécute afin de récupérer l'updater steam
	chmod +x hldsupdatetool.bin && ./hldsupdatetool.bin <<< "yes"
	
	# Il ne sert plus, on le supprime
	rm -f hldsupdatetool.bin
	
    # Puis on lance l'exécution de steam afin de mettre à jour l'exécutable
    ./steam
    sleep 1
    ./steam
    sleep 1

    # Et on lance l'install du jeu
    ./steam -command update -game "$JEU" -dir . -verify_all -retry
    sleep 1
    ./steam -command update -game "$JEU" -dir . -verify_all -retry
else
	echo "Usage: $0 game"
fi