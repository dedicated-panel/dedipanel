#!/bin/bash

URL_MM='http://ks380373.kimsufi.com/metamod.tar.gz'
URL_AMX='http://amxmod.net/amxfiles/amxmod_2010.1/amxmod_2010.1_lite-fr.zip'
URL_AMXX='http://ks380373.kimsufi.com/amxmodx.tar.gz'

URL_MMSRC='http://sourcemod.steamfriends.com/files/mmsource-1.8.7-linux.tar.gz'
URL_SRCM='http://sourcemod.steamfriends.com/files/sourcemod-1.3.8-linux.tar.gz'
URL_ES='http://oh.ms/es370linux'

PWD=`pwd`

case "$1" in
    mm)
        # On créer le dossier addons/metamod/dlls/
        mkdir -p $PWD/addons/metamod/dlls
        
        # Puis on télécharge l'archive, on la décompresse pour avoir le .so et on la supprime
        wget -O metamod.tar.gz $URL_MM && tar zxvf metamod.tar.gz && rm metamod.tar.gz
        # On déplace le .so vers son dossier
        mv metamod_i386.so $PWD/addons/metamod/dlls/metamod_i386.so

        # On modifie le liblist.gam 
        # On commence par commenter la ligne concernant windows (si nécessaire)
        sed -i '/^gamedll / s/gamedll/#gamedll/g' $PWD/liblist.gam
        # Puis on modifie celle concernant linux
        sed -i '/^gamedll_linux/ s/".\+"/"addons\/metamod\/dlls\/metamod_i386.so"/' $PWD/liblist.gam

        # On créer le fichier contenant la liste des plugins
        touch $PWD/addons/metamod/plugins.ini
    ;;

    amx)
        # On récupère l'archive & on la décompresse
        wget -O amx.zip $URL_AMX && unzip amx.zip

        # Puis on termine par activer le plugin dans la config de metamod
        echo 'linux addons/amx/dlls/amx_mm_i386.so' >> $PWD/addons/metamod/plugins.ini
    ;;

    amxx)
        # On dl amxmodx & on décompresse l'archive et on supprime l'archive
        wget -O amxx.tar.gz $URL_AMXX && tar zxvf amxx.tar.gz && rm amxx.tar.gz

        # On active amxx dans le fichier de config de metamod
        echo 'linux addons/amxmodx/dlls/amxmodx_mm_i386.so' >> $PWD/addons/metamod/plugins.ini
    ;;

    mmsrc)
        # On dl metamod:source & on le décompresse dans le dossier aproprie
        wget -O metamod.tar.gz $URL_MMSRC && tar zxvf metamod.tar.gz && rm metamod.tar.gz

        # On l'active en créant un fichier .vdf
        echo '"Plugin"
{
    "file"	"../addons/metamod/bin/server"
}' > $PWD/addons/metamod.vdf
    ;;

    srcm)
        # On dl sourcemod, on le décompresse et on supprime le fichier temporaire
        wget -O sourcemod.tar.gz $URL_SRCM && tar zxvf sourcemod.tar.gz && rm sourcemod.tar.gz
    ;;

    es)
        # Comme pour les autres, on dl, on décompresse et on supprime
        wget -O eventscripts.zip $URL_ES && unzip eventscripts.zip && rm eventscripts.zip

        if [ -f $PWD/cfg/autoexec.cfg ]; then
            AUTOEXEC=`cat cfg/autoexec.cfg`
        else
            AUTOEXEC=''
        fi

        echo 'mattie_eventscripts 1' > cfg/autoexec.cfg
        echo 'eventscripts_subdirectory events' >> cfg/autoexec.cfg
        echo "$AUTOEXEC" >> cfg/autoexec.cfg
    ;;
    
    *)
        echo "Usage: $0 {mm|amx|amxx|mmsrc|srcm|es}"
    ;;
esac