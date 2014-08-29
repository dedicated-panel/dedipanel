<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
