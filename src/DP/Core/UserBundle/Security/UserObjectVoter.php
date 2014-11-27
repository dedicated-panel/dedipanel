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

/**
 * Can vote against other user objects,
 * based on the roles and group of current user
 */
class UserObjectVoter extends AbstractObjectVoter
{
    protected function getSupportedClasses()
    {
        return ['DP\Core\UserBundle\Entity\User'];
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($this->supportsClass(get_class($object))) {
            // Deny access if the user try to edit/delete himself (except for super admin)
            if ($object === $token->getUser()
                && array_intersect(['ROLE_DP_ADMIN_USER_UPDATE', 'ROLE_DP_ADMIN_USER_DELETE'], $attributes) !== array()
                && !$token->getUser()->isSuperAdmin()) {
                return VoterInterface::ACCESS_DENIED;
            }

            /** @var \DP\Core\UserBundle\Entity\User $user */
            $user  = $token->getUser();
            $accessibleGroups = $this->getUserAccessibleGroups($user);

            /** @var \DP\Core\UserBundle\Entity\Group|null $group Direct group of the user against which we are voting */
            $group = $object->getGroup();

            if (($group !== null && in_array($group, $accessibleGroups))
                || $user->isSuperAdmin()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
