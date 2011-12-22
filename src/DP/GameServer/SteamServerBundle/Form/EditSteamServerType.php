<?php

namespace DP\GameServer\SteamServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EditSteamServerType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('machineId', 'number', array('label' => 'steam.selectMachine'))
            ->add('name', 'string', array('label' => 'steam.name'))
            ->add('port', 'number', array('label' => 'steam.port'))
            ->add('gameId', 'number', array('label' => 'steam.gameId'))
            ->add('dir', 'string', array('label' => 'steam.dir'))
            ->add('maxplayers', 'number', array('label' => 'steam.maxplayers'))
            ->add('autoReboot', 'number', array('label' => 'steam.autoReboot', 'required' => false))
            ->add('rcon', 'string', array('l'))
            ->add('munin')
            ->add('sv_passwd')
            ->add('core')
        ;
    }

    public function getName()
    {
        return 'dp_gameserver_steamserverbundle_steamservertype';
    }
}
