<?php

namespace DP\Core\UserBundle\EventListener;

use DP\Core\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UserAdminListener
{
    const USER_INDEX = 'dedipanel_user_index';

    private $context;

    /**
     * @param SecurityContextInterface $context
     */
    public function __construct(SecurityContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route') == static::USER_INDEX
        && $this->context->isGranted(User::ROLE_SUPER_ADMIN)) {
            $_sylius = $request->attributes->get('_sylius');
            unset($_sylius['criteria']['groups']);

            $request->attributes->set('_sylius', $_sylius);
        }
    }
}
