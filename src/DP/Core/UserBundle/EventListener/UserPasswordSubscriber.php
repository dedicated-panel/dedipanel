<?php

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
        return array(FormEvents::SUBMIT => 'submit');
    }
    
    public function submit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        
        if ($data->getId() == null) {
            if ($data->getPlainPassword() !== null) {
                $data->setPassword('');
            }
        }
    }
}
