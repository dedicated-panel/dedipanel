<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Extension;

class DebugExtension extends \Twig_Extension
{
    protected $debug;
    
    public function __construct($debug)
    {
        $this->debug = $debug;
    }
    
    public function getGlobals()
    {
        return array(
            'dedipanel' => array(
                'debug'   => $this->debug, 
            )
        );
    }
    
    public function getName()
    {
        return 'dedipanel_debug';
    }
}
