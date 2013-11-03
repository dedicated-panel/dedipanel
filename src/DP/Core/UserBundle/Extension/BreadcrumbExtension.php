<?php

namespace DP\Core\UserBundle\Extension;

use Knp\Menu\Twig\Helper;

class BreadcrumbExtension extends \Twig_Extension
{
    private $helper;

    /**
     * @param \Knp\Menu\Twig\Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    public function getFunctions()
    {
        return array(
            'breadcrumb_render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders a menu with the specified renderer.
     *
     * @param \Knp\Menu\ItemInterface|string|array $menu
     * @param array $options
     * @param string $renderer
     * @return string
     */
    public function render($menu, array $options = array(), $renderer = null)
    {
        $options = array_merge($options, array(
            'allow_safe_labels' => true, 
            'currentAsLink' => false, 
            'template' => 'DPCoreUserBundle::breadcrumb.html.twig', 
            'currentClass' => 'active', 
        ));
        
        return $this->helper->render($menu, $options, $renderer);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dp_breadcrumb';
    }  
}
