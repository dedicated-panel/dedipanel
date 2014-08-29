<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DPUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
