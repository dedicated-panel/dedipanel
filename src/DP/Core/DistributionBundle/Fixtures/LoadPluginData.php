<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\DistributionBundle\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use DP\Core\GameBundle\Entity\Plugin;

class LoadPluginData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $metamod = new Plugin();
        $metamod->setName('Metamod');
        $metamod->setDownloadUrl('http://www.dedicated-panel.net/metamod-1.21-am.tar.gz');
        $metamod->setScriptName('metamod');
        $metamod->setVersion('1.21-am');
        $manager->persist($metamod);
        
        $amxx = new Plugin();
        $amxx->setName('AMX Mod X (Core Addon)');
        $amxx->setDownloadUrl('http://www.amxmodx.org/dl.php?file_id=690&mirror_id=2');
        $amxx->setScriptName('amxmodx');
        $amxx->setVersion('1.8.2');
        $manager->persist($amxx);
        
        $amxxCS = new Plugin();
        $amxxCS->setName('AMX Mox X (Counter-Strike Addon)');
        $amxxCS->setDownloadUrl('http://www.amxmodx.org/dl.php?file_id=692&mirror_id=2');
        $amxxCS->setScriptName('amxmodx');
        $amxxCS->setVersion('1.8.2');
        $manager->persist($amxxCS);
        
        $amxxDOD = new Plugin();
        $amxxDOD->setName('AMX Mod X (Day of Defeat Addon)');
        $amxxDOD->setDownloadUrl('http://www.amxmodx.org/dl.php?file_id=694&mirror_id=2');
        $amxxDOD->setScriptName('amxmodx');
        $amxxDOD->setVersion('1.8.2');
        $manager->persist($amxxDOD);
        
        $amxxTFC = new Plugin();
        $amxxTFC->setName('AMX Mod X (Team Fortress Classic Addon)');
        $amxxTFC->setDownloadUrl('http://www.amxmodx.org/dl.php?file_id=700&mirror_id=2');
        $amxxTFC->setScriptName('amxmodx');
        $amxxTFC->setVersion('1.8.2');
        $manager->persist($amxxTFC);
        
        $mmSource = new Plugin();
        $mmSource->setName('Metamod:Source')
                 ->setDownloadUrl('http://sourcemod.gameconnect.net/files/mmsource-1.10.0-linux.tar.gz')
                 ->setScriptName('metamod_source')
                 ->setVersion('1.10.0');
        $manager->persist($mmSource);
        
        $sourcemod = new Plugin();
        $sourcemod->setName('Sourcemod')
            ->setDownloadUrl('http://sourcemod.gameconnect.net/files/sourcemod-1.5.0-linux.tar.gz')
            ->setScriptName('sourcemod')
            ->setVersion('1.5.0');
        $manager->persist($sourcemod);
        
        $manager->flush();
        
        $this->addReference('metamod', $metamod);
        $this->addReference('amxx', $amxx);
        $this->addReference('amxxCS', $amxxCS);
        $this->addReference('amxxDOD', $amxxDOD);
        $this->addReference('amxxTFC', $amxxTFC);
        $this->addReference('mmSource', $mmSource);
        $this->addReference('sourcemod', $sourcemod);
    }
    
    public function getOrder()
    {
        return 1;
    }
}
