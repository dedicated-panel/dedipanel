<?php

namespace DP\Admin\UserBundle\Security;

use DP\Admin\AdminBundle\Security\ChildRoleBuilderInterface;

class GroupRoleBuilder implements ChildRoleBuilderInterface
{
    public function getRole()
    {
        return 'ROLE_DP_ADMIN_GROUP_ADMIN';
    }
}
