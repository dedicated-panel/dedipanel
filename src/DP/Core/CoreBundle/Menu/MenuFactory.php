<?php

namespace DP\Core\CoreBundle\Menu;

use Knp\Menu\MenuFactory as BaseMenuFactory;
use DP\Core\CoreBundle\Menu\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MenuFactory extends BaseMenuFactory
{
    protected $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }
    
    public function createItem($name, array $options = array())
    {
        $item = new MenuItem($name, $this);

        $options = array_merge(
            array(
                'uri' => null,
                'label' => null,
                'attributes' => array(),
                'linkAttributes' => array(),
                'childrenAttributes' => array(),
                'labelAttributes' => array(),
                'extras' => array(),
                'display' => true,
                'displayChildren' => true,
                'pattern' => null, 
            ),
            $options
        );
        
        if (!empty($options['route'])) {            
            $params = isset($options['routeParameters']) ? $options['routeParameters'] : array();
            $absolute = isset($options['routeAbsolute']) ? $options['routeAbsolute'] : false;
            $options['uri'] = $this->generator->generate($options['route'], $params, $absolute);
            
            if (!isset($options['pattern']) || empty($options['pattern'])) {
                $options['pattern'] = $options['uri'];
            }
        }
        
        // Ajoute un dolar au pattern utilisé pour déterminer 
        // par regex s'il s'agit de l'item actuel
        if (!empty($options['pattern']) && isset($options['pattern_strict']) 
        && $options['pattern_strict'] === true) {
            $options['pattern'] .= '$';
        }

        $item
            ->setUri($options['uri'])
            ->setLabel($options['label'])
            ->setAttributes($options['attributes'])
            ->setLinkAttributes($options['linkAttributes'])
            ->setChildrenAttributes($options['childrenAttributes'])
            ->setLabelAttributes($options['labelAttributes'])
            ->setExtras($options['extras'])
            ->setDisplay($options['display'])
            ->setDisplayChildren($options['displayChildren'])
            ->setPattern($options['pattern'])
        ;

        return $item;
    }
}
