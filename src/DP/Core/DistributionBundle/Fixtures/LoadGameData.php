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
        $mcConfigTemplate = file_get_contents(__DIR__ . '/cfg/minecraft.cfg');
        $cssConfigTemplate = file_get_contents(__DIR__ . '/cfg/css.cfg');
        $tf2ConfigTemplate = file_get_contents(__DIR__ . '/cfg/tf2.cfg');
        
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
        $cs->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/cs/');
        $cs->setSource(false);
        $cs->setType('steam');
        $cs->addPlugin($this->getReference('metamod'));
        $cs->addPlugin($this->getReference('amxx'));
        $cs->addPlugin($this->getReference('amxxCS'));
        $manager->persist($cs);

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
        $cz->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/czero/');
        $cz->setType('steam');
        $cz->setSource(false);
        $cz->addPlugin($this->getReference('metamod'));
        $cz->addPlugin($this->getReference('amxx'));
        $cz->addPlugin($this->getReference('amxxCS'));
        $manager->persist($cz);

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
        $dod->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/dod/');
        $dod->setType('steam');
        $dod->setSource(false);
        $dod->addPlugin($this->getReference('metamod'));
        $dod->addPlugin($this->getReference('amxx'));
        $dod->addPlugin($this->getReference('amxxDOD'));
        $manager->persist($dod);

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
        $tfc->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/tfc/');
        $tfc->setType('steam');
        $tfc->setSource(false);
        $tfc->addPlugin($this->getReference('metamod'));
        $tfc->addPlugin($this->getReference('amxx'));
        $tfc->addPlugin($this->getReference('amxxTFC'));
        $manager->persist($tfc);

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
        $css->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/css/');
        $css->setType('steam');
        $css->setSource(true);
        $css->setConfigTemplate($cssConfigTemplate);
        $css->addPlugin($this->getReference('mmSource'));
        $css->addPlugin($this->getReference('sourcemod'));
        $manager->persist($css);

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
        $tf->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/tf2/');
        $tf->setType('steam');
        $tf->setSource(true);
        $tf->setConfigTemplate($tf2ConfigTemplate);
        $tf->addPlugin($this->getReference('mmSource'));
        $tf->addPlugin($this->getReference('sourcemod'));
        $manager->persist($tf);

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
        $dods->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/dods/');
        $dods->setType('steam');
        $dods->setSource(true);
        $dods->addPlugin($this->getReference('mmSource'));
        $dods->addPlugin($this->getReference('sourcemod'));
        $manager->persist($dods);

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
        $csgo->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/l4d/');
        $csgo->setAppId('740');
        $csgo->setType('steam');
        $csgo->setAvailable(true);
        $csgo->addPlugin($this->getReference('mmSource'));
        $csgo->addPlugin($this->getReference('sourcemod'));
        $manager->persist($csgo);

        $l4d = new Game();
        $l4d->setName('Left 4 Dead');
        $l4d->setInstallName('left4dead');
        $l4d->setLaunchName('left4dead');
        $l4d->setBin('srcds_run');
        $l4d->setOrangebox(true);
        $l4d->setMap('l4d_hospital01_apartment');
        $l4d->setAvailable(true);
        $l4d->setBinDir('l4d/');
        $l4d->setSource(true);
        $l4d->setOrangebox(false);
        $l4d->setSteamCmd(false);
        $l4d->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/l4d/');
        $l4d->setType('steam');
        $l4d->addPlugin($this->getReference('mmSource'));
        $l4d->addPlugin($this->getReference('sourcemod'));
        $manager->persist($l4d);

        $l4d2 = new Game();
        $l4d2->setName('Left 4 Dead 2');
        $l4d2->setInstallName('left4dead2');
        $l4d2->setLaunchName('left4dead2');
        $l4d2->setBin('srcds_run');
        $l4d2->setOrangebox(true);
        $l4d2->setMap('c1m1_hotel');
        $l4d2->setAvailable(true);
        $l4d2->setBinDir('left4dead2/');
        $l4d2->setSource(true);
        $l4d2->setOrangebox(false);
        $l4d2->setSteamCmd(false);
        $l4d2->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/left4dead2/');
        $l4d2->setType('steam');
        $l4d2->addPlugin($this->getReference('mmSource'));
        $l4d2->addPlugin($this->getReference('sourcemod'));
        $manager->persist($l4d2);

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
        $mc->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/minecraft/');
        $mc->setType('minecraft');
        $mc->setConfigTemplate($mcConfigTemplate);
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
        $bukkit->setSourceImagesMaps('http://image.www.gametracker.com/images/maps/160x120/minecraft/');
        $bukkit->setType('minecraft');
        $bukkit->setConfigTemplate($mcConfigTemplate);
        $manager->persist($bukkit);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
