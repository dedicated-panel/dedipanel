<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use DP\Core\MachineBundle\Entity\Machine;

class MachineObjectVoter extends AbstractObjectVoter
{
    protected function getSupportedClasses()
    {
        return ['DP\Core\MachineBundle\Entity\Machine'];
    }

    public function voting(TokenInterface $token, $object, array $attributes)
    {
        /** @var \DP\Core\UserBundle\Entity\User $user */
        $user = $token->getUser();
        $objectGroups     = iterator_to_array($object->getGroups());
        $accessibleGroups = $this->getUserAccessibleGroups($user);

        if (array_intersect($objectGroups, $accessibleGroups) !== array()
        || $user->isSuperAdmin()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
