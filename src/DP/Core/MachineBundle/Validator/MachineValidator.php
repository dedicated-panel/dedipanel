<?php

namespace DP\Core\MachineBundle\Validator;

use DP\Core\MachineBundle\Entity\Machine;
use Symfony\Component\Validator\ExecutionContextInterface;
use DP\Core\MachineBundle\PHPSeclibWrapper\PHPSeclibWrapper;

class MachineValidator
{
    public static function validateNotEmptyPassword(Machine $machine, ExecutionContextInterface $context)
    {
        if (null === $machine->getId() && null === $machine->getPassword()) {
            $context->addViolation('machine.assert.password');
        }
    }
    
    public static function validateCredentials(Machine $machine, ExecutionContextInterface $context)
    {
        // N'exécute pas la validation des identifiants
        // s'il y a déjà eu des erreurs
        if ($context->getViolations() === 0) {
            $havePassword = null !== $machine->getPassword();
            
            // @todo: refacto phpseclib
            $secure = PHPSeclibWrapper::getFromMachineEntity($machine, !$havePassword);
            
            if ($havePassword) {
                $secure->setPasswd($machine->getPassword());
            }
            
            $test = $secure->connectionTest();
            
            if (!$test) {
                $context->addViolation('machine.assert.bad_credentials');
            }
        }
    }
}
