<?php

namespace DP\Core\MachineBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionManagerInterface;
use Dedipanel\PHPSeclibWrapperBundle\Connection\Exception\ConnectionErrorException;


class CredentialsConstraintValidator extends ConstraintValidator
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
        if (count($this->context->getViolations()) === 0) {
            $conn = $this->manager->getConnectionFromServer($value, 0);
            $test = false;

            try {
                $test = $conn->testSSHConnection();
            }
            catch (ConnectionErrorException $e) {
                // The test failed
            }

            if (!$test) {
                $this->context->addViolation('machine.assert.bad_credentials');
            }
        }
    }
}
