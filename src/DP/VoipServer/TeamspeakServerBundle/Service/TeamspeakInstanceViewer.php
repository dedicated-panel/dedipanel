<?php

namespace DP\VoipServer\TeamspeakServerBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerInstance;

class TeamspeakInstanceViewer extends \Twig_Extension
{
    protected $container;

    /**
     * As we can't use 'templating.helper.assets' service directly
     * (it's on a narrower scope), we need to inject the whole service container
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('ts_viewer', array($this, 'getView')),
        ];
    }

    public function getView(TeamspeakServerInstance $instance)
    {
        $helper = $this->container->get('templating.helper.assets');
        $baseUrl = $helper->getUrl('bundles/dpteamspeakserver/images/');

        $iconPath = $baseUrl . 'viewer/';
        $flagPath = $baseUrl . 'flags/';

        $viewer = new \TeamSpeak3_Viewer_Html($iconPath, $flagPath);
        $instance = $instance->getQuery()->getInstance($instance->getInstanceId());

        return $instance->getViewer($viewer);
    }

    public function getName()
    {
        return 'dedipanel_teamspeak_instance_viewer';
    }
}
