<?php

namespace DP\Core\DistributionBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class InstallerVoter implements VoterInterface
{
    const ATTR = 'ROLE_INSTALLER_USER';

    /** @var \Symfony\Component\HttpFoundation\Request $request */
    private $request;

    /** @var array $whitelisted Contains the list of authorized IP address */
    private $whitelisted;

    public function __construct(ContainerInterface $container, $filepath)
    {
        $this->request     = $container->get('request');
        $this->whitelisted = [];

        if (file_exists($filepath)) {
            $this->whitelisted = explode(PHP_EOL, file_get_contents($filepath));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return self::ATTR === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * {@inheritodc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes AS $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if (in_array($this->request->getClientIp(), $this->whitelisted)) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
