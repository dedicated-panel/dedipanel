<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
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
     * Delete sylius criteria on dedipanel_user_index for super admin
     * to ensure that they can view all users (including other super admin)
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
