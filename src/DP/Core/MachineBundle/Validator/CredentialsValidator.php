<?php

namespace DP\Core\MachineBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Dedipanel\PHPSeclibWrapperBundle\Server\ServerInterface;
use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionManagerInterface;

class CredentialsValidator extends ConstraintValidator
{
    protected $manager;
    
    public function __construct(ConnectionManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    
    public function validate($value, Constraint $constraint)
    {
        // N'exécute pas la validation des identifiants
        // s'il y a déjà eu des erreurs
        if ($this->context->getViolations() === 0) {
            $conn = $this->manager->getConnectionFromServer($server, 0);
            
            if (!$conn->connectionTest()) {
                $context->addViolation('machine.assert.bad_credentials');
            }
        }
    }
}
