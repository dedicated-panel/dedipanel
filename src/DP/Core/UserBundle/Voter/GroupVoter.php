<?php

namespace DP\Core\UserBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Psr\Log\LoggerInterface;

class GroupVoter implements VoterInterface
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function supportsAttribute($attribute)
    {
        return preg_match('#^DP_#', $attribute);
    }
    
    public function supportsClass($class)
    {
        var_dump($class);
        exit();
        
        return true;
    }
    
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        
    }
}
