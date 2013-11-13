<?php

namespace DP\Admin\UserBundle\Security;

use DP\Admin\AdminBundle\Security\ChildRoleBuilderInterface;

class UserRoleBuilder implements ChildRoleBuilderInterface
{
    public function getRole()
    {
        return 'ROLE_DP_ADMIN_USER_ADMIN';
    }
}
