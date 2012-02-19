<?php

namespace DP\Core\DistributionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DPDistributionBundle extends Bundle
{
    public function getParent()
    {
        return 'SensioDistributionBundle';
    }
}
