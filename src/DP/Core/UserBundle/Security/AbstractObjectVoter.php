<?php

namespace DP\Core\UserBundle\Security;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use DP\Core\UserBundle\Entity\GroupRepository;
use DP\Core\UserBundle\Entity\User;

/**
 * Abstract voter implementation used for voter that will vote against others objects
 */
abstract class AbstractObjectVoter implements VoterInterface
{
    /**
     * @var \DP\Core\UserBundle\Entity\GroupRepository
     */
    protected $groupRepo;

    /**
     * Will vote against $object. Will return either ACCESS_GRANTED or ACCESS_DENIED
     * Useful for testing purpose (see UserObjectVoterTest)
     *
     * @param TokenInterface $token
     * @param $object
     * @param array $attributes
     * @return bool
     */
    abstract protected function voting(TokenInterface $token, $object, array $attributes);

    /**
     * @return array
     */
    abstract protected function getSupportedClasses();

    public function __construct(GroupRepository $groupRepo)
    {
        $this->groupRepo = $groupRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($this->supportsClass(get_class($object))) {
            return $this->voting($token, $object, $attributes);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function supportsClass($class)
    {
        foreach ($this->getSupportedClasses() as $supportedClass) {
            if ($supportedClass === $class || is_subclass_of($class, $supportedClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return (bool) preg_match('#^ROLE_DP_#', $attribute);
    }

    public function getUserAccessibleGroups(User $user, $includeNode = true)
    {
        // On vérifie que l'utilisateur est soit un super admin,
        // soit assigné à un groupe puisque s'il ne l'est pas,
        // $accessibleGroups contient automatiquement tous les groupes
        // (cf. méthode getChildren())
        if ($user->getGroup() === null && !$user->isSuperAdmin()) {
            throw new \RuntimeException(sprintf('User "%s" is not super admin and is not assigned to a group.', $user));
        }

        return $this->groupRepo
            ->getChildren($user->getGroup(), false, null, "asc", $includeNode);
    }
}
