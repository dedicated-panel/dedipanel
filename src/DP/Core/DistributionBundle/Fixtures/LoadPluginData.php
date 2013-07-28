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
        $metamod->setDownloadUrl('http://www.dedicated-panel.net/metamod.tar.gz');
        $metamod->setScriptName('metamod');
        $manager->persist($metamod);
        
        $amxx = new Plugin();
        $amxx->setName('AMX Mod X (Core Addon)');
        $amxx->setDownloadUrl('http://www.amxmodx.org/dl.php?file_id=690&mirror_id=2');
        $amxx->setScriptName('amxmodx');
        $manager->persist($amxx);
        
        $amxxCS = new Plugin();
        $amxxCS->setName('AMX Mox X (Counter-Strike Addon)');
        $amxxCS->setDownloadUrl('http://www.amxmodx.org/dl.php?file_id=692&mirror_id=2');
        $amxxCS->setScriptName('amxmodx');
        $manager->persist($amxxCS);
        
        $amxxDOD = new Plugin();
        $amxxDOD->setName('AMX Mod X (Day of Defeat Addon)');
        $amxxDOD->setDownloadUrl('http://www.amxmodx.org/dl.php?file_id=694&mirror_id=2');
        $amxxDOD->setScriptName('amxmodx');
        $manager->persist($amxxDOD);
        
        $amxxTFC = new Plugin();
        $amxxTFC->setName('AMX Mod X (Team Fortress Classic Addon)');
        $amxxTFC->setDownloadUrl('http://www.amxmodx.org/dl.php?file_id=700&mirror_id=2');
        $amxxTFC->setScriptName('amxmodx');
        $manager->persist($amxxTFC);
        
        $manager->flush();
        
        $this->addReference('metamod', $metamod);
        $this->addReference('amxx', $amxx);
        $this->addReference('amxxCS', $amxxCS);
        $this->addReference('amxxDOD', $amxxDOD);
        $this->addReference('amxxTFC', $amxxTFC);
    }
    
    public function getOrder()
    {
        return 1;
    }
}
