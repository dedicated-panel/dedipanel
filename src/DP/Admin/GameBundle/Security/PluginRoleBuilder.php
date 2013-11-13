<?php

namespace DP\Admin\GameBundle\Security;

use DP\Admin\AdminBundle\Security\ChildRoleBuilderInterface;

class PluginRoleBuilder implements ChildRoleBuilderInterface
{
    public function getRole()
    {
        return 'ROLE_DP_ADMIN_PLUGIN_ADMIN';
    }
}
