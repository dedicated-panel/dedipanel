<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     *
     * Delete sylius criteria for super admin
     * to ensure that they can view all entities (for instance, servers assigned to any group)
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $_sylius = $request->attributes->get('_sylius');

        if (null !== $this->context->getToken()
            && $this->context->isGranted(User::ROLE_SUPER_ADMIN)
            && isset($_sylius['criteria']['groups'])
        ) {
            unset($_sylius['criteria']['groups']);

            $request->attributes->set('_sylius', $_sylius);
        }
    }
}
