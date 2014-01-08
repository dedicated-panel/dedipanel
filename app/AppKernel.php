<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            
            // new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(), 
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            
            new FOS\UserBundle\FOSUserBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            
            new DP\Core\UserBundle\DPCoreUserBundle(),
            new DP\Core\GameBundle\DPGameBundle(),
            new DP\Core\MachineBundle\DPMachineBundle(),
            
            new DP\Admin\AdminBundle\DPAdminBundle(),
            
            new DP\GameServer\GameServerBundle\DPGameServerBundle(),
            new DP\GameServer\SteamServerBundle\DPSteamServerBundle(),
            new DP\GameServer\MinecraftServerBundle\DPMinecraftServerBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            
            $bundles[] = new DP\Core\DistributionBundle\DPDistributionBundle();
        }
        
        if ($this->getEnvironment() == 'installer') {
            $bundles[] = new DP\Core\DistributionBundle\DPDistributionBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
