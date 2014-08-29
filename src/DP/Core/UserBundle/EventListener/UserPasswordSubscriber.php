<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Permet lors de la création d'un nouvel utilisateur, 
 * d'initialiser la propriété password de l'entité 
 * si un mot de passe (propriété plainPassword) a été renseigné
 */
class UserPasswordSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'dynamicPasswordField',
            FormEvents::SUBMIT       => 'submit',
        );
    }

    /**
     * If the current user already exists,
     * Replace the plainPassword field for changing
     * "required" from true => false
     *
     * @param FormEvent $event
     */
    public function dynamicPasswordField(FormEvent $event)
    {
        $user = $event->getData();
        $form = $event->getForm();

        if ($user->getId() !== null) {
            $form->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'first_options' => array('label'  => 'user.fields.password'),
                'second_options' => array('label' => 'user.fields.repeat_password'),
                'required' => false,
            ));
        }
    }

    /**
     * Set an empty password if the plainPassword has been filled
     *
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        $user = $event->getData();
        $form = $event->getForm();
        
        if ($user->getId() == null && $user->getPlainPassword() !== null) {
            $user->setPassword('');
        }
    }
}
