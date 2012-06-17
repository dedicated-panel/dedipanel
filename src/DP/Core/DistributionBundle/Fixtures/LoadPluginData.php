<?php

/*
** Copyright (C) 2010-2012 Kerouanton Albin, Smedts Jérôme
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
        $metamod->setDownloadUrl('http://ks380373.kimsufi.com/metamod.tar.gz');
        $metamod->setArchiveType('tar.gz');
        $metamod->setScriptName('metamod');
        $manager->persist($metamod);
        $this->addReference('metamod', $metamod);
        
        $amxx = new Plugin();
        $amxx->setName('AMX Mod X');
        $amxx->setDownloadUrl('http://www.amxmodx.org/dl.php?filename=amxmodx-1.8.1-base.tar.gz');
        $amxx->setArchiveType('tar.gz');
        $amxx->setScriptName('amxmodx');
        $manager->persist($amxx);
        $this->addReference('amxx', $amxx);
        
        $amxCs = new Plugin();
        $amxCs->setName('AMX Mod (CS/CZ)');
        $amxCs->setDownloadUrl('http://www.amxmod.net/amxfiles/amxmod_2010.1/amxmod_2010.1_cs-fr.zip');
        $amxCs->setArchiveType('zip');
        $amxCs->setScriptName('amxmod');
        $manager->persist($amxCs);
        $this->addReference('amxCs', $amxCs);
        
        $amxDod = new Plugin();
        $amxDod->setName('AMX Mod (DoD)');
        $amxDod->setDownloadUrl('http://www.amxmod.net/amxfiles/amxmod_2010.1/amxmod_2010.1_dod-fr.zip');
        $amxDod->setArchiveType('zip');
        $amxDod->setScriptName('amxmod');
        $manager->persist($amxDod);
        $this->addReference('amxDod', $amxDod);
        
        $amx = new Plugin();
        $amx->setName('AMX Mod (Lite)');
        $amx->setDownloadUrl('http://www.amxmod.net/amxfiles/amxmod_2010.1/amxmod_2010.1_lite-fr.zip');
        $amx->setArchiveType('zip');
        $amx->setScriptName('amxmod');
        $manager->persist($amx);
        $this->addReference('amx', $amx);
    }
    public function getOrder()
    {
        return 0;
    }
}
