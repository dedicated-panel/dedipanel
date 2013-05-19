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
use DP\Core\GameBundle\Entity\Game;

class LoadGameData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $cs = new Game();
        $cs->setName('Counter Strike');
        $cs->setInstallName('cstrike');
        $cs->setLaunchName('cstrike');
        $cs->setBin('hlds_run');
        $cs->setOrangebox(false);
        $cs->setMap('de_dust2');
        $cs->setAvailable(true);
        $cs->setBinDir('');
        $cs->setSteamCmd(true);
        $cs->setAppId('90');
        $cs->setAppMod('cstrike');
//        $cs->setSourceImagesMaps('');
        $cs->setSource(false);
        $cs->setType('steam');
        $cs->addPlugin($this->getReference('metamod'));
        $cs->addPlugin($this->getReference('amxCs'));
        $manager->persist($cs);
//        $this->addReference('cs', $cs);

        $cz = new Game();
        $cz->setName('Counter-Strike: Condition Zéro');
        $cz->setInstallName('czero');
        $cz->setLaunchName('czero');
        $cz->setBin('hlds_run');
        $cz->setOrangebox(false);
        $cz->setMap('de_dust2');
        $cz->setAvailable(true);
        $cs->setBinDir('');
        $cz->setSteamCmd(true);
        $cz->setAppId('90');
        $cz->setAppMod('czero');
//        $cz->setSourceImageMaps('');
        $cz->setType('steam');
        $cz->setSource(false);
        $cz->addPlugin($this->getReference('metamod'));
        $cz->addPlugin($this->getReference('amxCs'));
        $manager->persist($cz);
//        $this->addReference('cz', $cz);

        $dod = new Game();
        $dod->setName('Day of Defeat');
        $dod->setInstallName('dod');
        $dod->setLaunchName('dod');
        $dod->setBin('hlds_run');
        $dod->setOrangebox(false);
        $dod->setMap('dod_anzio');
        $dod->setAvailable(true);
        $dod->setBinDir('');
        $dod->setSteamCmd(true);
        $dod->setAppId('90');
        $dod->setAppMod('dod');
//        $dod->setSourceImageMaps('');
        $dod->setType('steam');
        $dod->setSource(false);
        $dod->addPlugin($this->getReference('metamod'));
        $dod->addPlugin($this->getReference('amxDod'));
        $manager->persist($dod);
//        $this->addReference('dod', $dod);

        $tfc = new Game();
        $tfc->setName('Team Fortress Classic');
        $tfc->setInstallName('tfc');
        $tfc->setLaunchName('tfc');
        $tfc->setBin('hlds_run');
        $tfc->setOrangebox(false);
        $tfc->setMap('2fort');
        $tfc->setAvailable(true);
        $tfc->setBinDir('');
        $tfc->setSteamCmd(true);
        $tfc->setAppId('90');
        $tfc->setAppMod('tfc');
//        $tfc->setSourceImageMaps('');
        $tfc->setType('steam');
        $tfc->setSource(false);
        $tfc->addPlugin($this->getReference('metamod'));
        $tfc->addPlugin($this->getReference('amx'));
        $manager->persist($tfc);
//        $this->addReference('tfc', $tfc);

        /********************
         * Orangebox/Source *
         ********************/
        $css = new Game();
        $css->setName('Counter-Strike: Source');
        $css->setInstallName('Counter-Strike Source');
        $css->setLaunchName('cstrike');
        $css->setBin('srcds_run');
        $css->setOrangebox(false);
        $css->setMap('de_dust2');
        $css->setAvailable(true);
        $css->setBinDir('');
        $css->setSteamCmd(true);
        $css->setAppId('232330');
//        $css->setSourceImageMaps('');
        $css->setType('steam');
        $css->setSource(true);
        $manager->persist($css);
//        $this->addReference('css', $css);

        $tf = new Game();
        $tf->setName('Team Fortress 2');
        $tf->setInstallName('tf');
        $tf->setLaunchName('tf');
        $tf->setBin('srcds_run');
        $tf->setOrangebox(false);
        $tf->setMap('ctf_2fort');
        $tf->setAvailable(true);
        $tf->setBinDir('');
        $tf->setSteamCmd(true);
        $tf->setAppId('232250');
//        $tf->setSourceImageMaps('');
        $tf->setType('steam');
        $tf->setSource(true);
        $manager->persist($tf);
//        $this->addReference('tf', $tf);

        $dods = new Game();
        $dods->setName('Day of Defeat: Source');
        $dods->setInstallName('dod');
        $dods->setLaunchName('dod');
        $dods->setBin('srcds_run');
        $dods->setOrangebox(false);
        $dods->setMap('dod_anzio');
        $dods->setAvailable(true);
        $dods->setBinDir('');
        $dods->setSteamCmd(true);
        $dods->setAppId('232290');
//        $dods->setSourceImageMaps('');
        $dods->setType('steam');
        $dods->setSource(true);
        $manager->persist($dods);
//        $this->addReference('dods', $dods);

        $csgo = new Game();
        $csgo->setName('Counter-Strike: Global Offensive');
        $csgo->setInstallName('csgo');
        $csgo->setLaunchName('csgo');
        $csgo->setBin('srcds_run');
        $csgo->setBinDir('');
        $csgo->setMap('de_dust2');
        $csgo->setSource(true);
        $csgo->setOrangebox(false);
        $csgo->setSteamCmd(true);
        $csgo->setAppId('740');
        $csgo->setType('steam');
        $csgo->setAvailable(true);
        $manager->persist($csgo);

        /********************
         * Minecraft *
         ********************/
        $mc = new Game();
        $mc->setName('Minecraft');
        $mc->setInstallName('minecraft');
        $mc->setLaunchName('minecraft');
        $mc->setBin('minecraft_server.jar');
        $mc->setOrangebox(false);
        $mc->setSource(false);
        $mc->setMap('world');
        $mc->setAvailable(true);
        $mc->setBinDir('./');
//        $mc->setSourceImagesMaps('');
        $mc->setType('minecraft');
        $manager->persist($mc);

        $bukkit = new Game();
        $bukkit->setName('Minecraft Bukkit');
        $bukkit->setInstallName('bukkit');
        $bukkit->setLaunchName('bukkit');
        $bukkit->setBin('craftbukkit.jar');
        $bukkit->setOrangebox(false);
        $bukkit->setSource(false);
        $bukkit->setMap('world');
        $bukkit->setAvailable(true);
        $bukkit->setBinDir('./');
//        $bukkit->setSourceImagesMaps('');
        $bukkit->setType('minecraft');
        $manager->persist($bukkit);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
