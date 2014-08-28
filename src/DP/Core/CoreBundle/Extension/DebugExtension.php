<?php

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
