<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\TeamspeakServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TeamspeakServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer $teamspeak */
        $teamspeak = $builder->getData();

        $builder
            ->add('machine', 'dedipanel_machine_entity')
            ->add('dir', 'text', ['label' => 'game.dir'])
            ->add('voice_port', 'number', ['label' => 'voip.voice_port'])
            ->add('query_port', 'number', ['label' => 'voip.query_port'])
            ->add('query_password', 'password', ['label' => 'voip.query_password'])
            ->add('filetransfer_port', 'number', ['label' => 'teamspeak.filetransfer_port'])
            ->add('licence_file', 'file', [
                'label'    => 'teamspeak.licence',
                'required' => false,
            ])
            ->add('core', 'dedipanel_core_assignment', ['machine' => $teamspeak->getMachine()])
            ->add('alreadyInstalled', 'dictionary', [
                'name'     => 'yes_no',
                'label'    => 'game.isAlreadyInstalled',
                'expanded' => true
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'remove_on_create'  => ['core'],
                'remove_on_update'  => ['alreadyInstalled'],
                'disable_on_update' => ['machine', 'dir'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dedipanel_teamspeak';
    }
}
