<?php

namespace DP\Core\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DPCoreUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
