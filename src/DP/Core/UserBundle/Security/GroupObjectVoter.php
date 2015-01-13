<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Vote against Group object.
 * Classical role voter can grant or deny access based on the user's role.
 * (see access decision manager strategy)
 */
class GroupObjectVoter extends AbstractObjectVoter
{
    protected function getSupportedClasses()
    {
        return ['DP\Core\UserBundle\Entity\Group'];
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($this->supportsClass(get_class($object))) {
            // Deny access if the user try to edit/delete group on which he is directly assigned
            if ($token->getUser()->getGroup() === $object
                && array_intersect(['ROLE_DP_ADMIN_GROUP_UPDATE', 'ROLE_DP_ADMIN_GROUP_DELETE'], $attributes) !== array()) {
                return VoterInterface::ACCESS_DENIED;
            }

            /** @var \DP\Core\UserBundle\Entity\User $user */
            $user = $token->getUser();
            $accessibleGroups = $this->getUserAccessibleGroups($token->getUser(), false);

            if (in_array($object, $accessibleGroups) || $user->isSuperAdmin()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
