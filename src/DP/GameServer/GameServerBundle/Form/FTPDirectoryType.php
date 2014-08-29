<?php

namespace DP\GameServer\GameServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FTPDirectoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'game.ftp.dirname', 
            ))
        ;
    }
    
    public function getName()
    {
        return 'dedipanel_game_ftp_directory';
    }
}
