<?php

namespace DP\GameServer\SteamServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class SteamServerType extends AbstractType
{    
    public function buildForm(FormBuilder $builder, array $options)
    {
        
        $builder
            ->add('machine', 'entity', array(
                'label' => 'steam.selectMachine', 'class' => 'DPMachineBundle:Machine'))
            ->add('name', 'text', array('label' => 'steam.name'))
            ->add('port', 'number', array('label' => 'steam.port'))
            ->add('game', 'entity', array(
                'label' => 'steam.selectGame', 'class' => 'DPGameBundle:Game'))
            ->add('dir', 'text', array('label' => 'steam.dir'))
            ->add('maxplayers', 'number', array('label' => 'steam.maxplayers'))
        ;
    }

    public function getName()
    {
        return 'dp_gameserver_steamserverbundle_addsteamservertype';
    }
}
