<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;
use Doctrine\Common\Persistence\ObjectManager;
use DP\Core\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;

class UserAdminListener implements EventSubscriberInterface
{
    private $manager;
    
    public static function getSubscribedEvents()
    {
        return array(
            'dedipanel.user.pre_create' => 'cleanUpdate',
            'dedipanel.user.pre_update' => 'cleanUpdate',
        );
    }

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Update canonical fields and other internal fields of user
     * whitout flushing it
     *
     * @param ResourceEvent $event
     */
    public function cleanUpdate(ResourceEvent $event)
    {
        $this->manager->updateUser($event->getSubject(), false);
    }
}
