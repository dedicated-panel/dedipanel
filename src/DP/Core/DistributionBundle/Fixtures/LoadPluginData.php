<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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
                 ->setDownloadUrl('http://sourcemod.gameconnect.net/files/mmsource-1.9.2-linux.tar.gz')
                 ->setScriptName('metamod_source')
                 ->setVersion('1.9.2');
        $manager->persist($mmSource);
        
        $sourcemod = new Plugin();
        $sourcemod->setName('Sourcemod')
            ->setDownloadUrl('http://sourcemod.gameconnect.net/files/sourcemod-1.4.7-linux.tar.gz')
            ->setScriptName('sourcemod')
            ->setVersion('1.4.7');
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
