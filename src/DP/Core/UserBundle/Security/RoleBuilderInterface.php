<?php

namespace DP\Core\UserBundle\Security;

interface RoleBuilderInterface
{
    /**
     * @return string Base role name
     */
    public function getBaseRole();
    
    /**
     * @return array Role hierarchy
     */
    public function getRoleHierarchy();
}
