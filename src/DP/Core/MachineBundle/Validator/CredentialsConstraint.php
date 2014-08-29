<?php

namespace DP\Core\MachineBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CredentialsConstraint extends Constraint
{
    public function validatedBy()
    {
        return 'machine_credentials_validator';
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
