<?php

namespace DP\Core\UserBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

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
        $accessibleGroups = $this->getUserAccessibleGroups($user);

        if (in_array($object, $accessibleGroups) || $user->isSuperAdmin()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
