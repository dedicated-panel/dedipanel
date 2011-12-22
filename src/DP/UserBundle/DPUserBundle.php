<?php

namespace DP\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DPUserBundle extends Bundle {
    public function getParent() {
            return 'FOSUserBundle';
    }
}
