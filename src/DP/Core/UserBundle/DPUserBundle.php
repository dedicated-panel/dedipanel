<?php

namespace DP\Core\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DP\Core\UserBundle\DependencyInjection\Compiler\AddChildrenRoleBuilderCompilerPass;
use DP\Core\UserBundle\DependencyInjection\Compiler\AddDedipanelRolesCompilerPass;
use DP\Core\UserBundle\DependencyInjection\Compiler\RemoveTopRoleBuilderCompilerPass;

class DPUserBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass(new AddChildrenRoleBuilderCompilerPass);
        $builder->addCompilerPass(new AddDedipanelRolesCompilerPass);
        $builder->addCompilerPass(new RemoveTopRoleBuilderCompilerPass);
    }
    
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
