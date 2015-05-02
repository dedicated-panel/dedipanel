<?php

namespace DP\Core\CoreBundle\Behat;

use Behat\Gherkin\Node\TableNode;
use DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer;
use DP\GameServer\SteamServerBundle\Entity\SteamServer;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerInstance;
use Sylius\Bundle\ResourceBundle\Behat\DefaultContext as SyliusDefaultContext;

class ResourceContext extends SyliusDefaultContext
{
    /**
     * @var array
     */
    protected $users = [];

    /**
     * @Given /^there are following users:$/
     */
    public function thereAreFollowingUsers(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsUser(
                $data['username'],
                $data['email'],
                $data['password'],
                isset($data['role']) ? $data['role'] : 'ROLE_USER',
                isset($data['enabled']) ? $data['enabled'] : true,
                isset($data['group']) && !empty($data['group']) ? $data['group'] : null,
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsUser($username, $email, $password, $role = null, $enabled = true, $group = null, $flush = true)
    {
        if (null === $user = $this->getRepository('user')->findOneBy(array('username' => $username))) {
            /* @var $user \DP\Core\UserBundle\Entity\User */
            $user = $this->getRepository('user')->createNew();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setEnabled($enabled);
            $user->setPlainPassword($password);

            if (null !== $role) {
                $user->addRole($role);
            }

            if ($group !== null) {
                $group = $this->thereIsGroup($group);
                $user->setGroup($group);
            }

            $this->validate($user);
            $this->getEntityManager()->persist($user);

            if ($flush) {
                $this->getEntityManager()->flush();
            }

            $this->users[$username] = $password;
        }

        return $user;
    }

    /**
     * @Given /^there are following groups:$/
     */
    public function thereAreFollowingGroups(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsGroup(
                $data['name'],
                isset($data['roles']) ? array_map('trim', explode(',', $data['roles'])) : array(),
                !empty($data['parent']) ? $data['parent'] : null
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsGroup($name, array $roles = array(), $parent = null, $flush = true)
    {
        if (null === $group = $this->getRepository('group')->findOneBy(array('name' => $name))) {
            /* @var $group \DP\Core\UserBundle\Entity\Group */
            $group = $this->getRepository('group')->createNew();
            $group->setName($name);
            $group->setRoles($roles);

            if ($parent !== null) {
                $parent = $this->thereIsGroup($parent);
                $group->setParent($parent);
                $parent->addChildren($group);

                $this->getEntityManager()->persist($parent);
            }

            $this->validate($group);

            $this->getEntityManager()->persist($group);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $group;
    }

    /**
     * @Given /^there are following machines:$/
     */
    public function thereAreFollowingMachines(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $groups = isset($data['groups']) ? $data['groups'] : $data['group'];

            if (!empty($groups)) {
                $groups = array_map('trim', explode(',', $groups));
            } else {
                $groups = [];
            }

            $this->thereIsMachine(
                $data['username'],
                $data['privateIp'],
                $data['key'],
                $groups,
                (isset($data['is64Bit']) ? $data['is64Bit'] == 'yes' : false),
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsMachine($username, $privateIp = null, $privateKey = null, $groups = array(), $is64Bit = false, $flush = true)
    {
        if (null === $machine = $this->getRepository('machine')->findOneBy(array('username' => $username))) {
            $machine = $this->getRepository('machine')->createNew();
            $machine->setIp($privateIp);
            $machine->setUsername($username);
            $machine->setPrivateKeyName($privateKey);
            $machine->setHome('/home/' . $username);
            $machine->setIs64Bit($is64Bit);

            foreach ($groups AS $group) {
                $group = $this->thereIsGroup($group);
                $machine->addGroup($group);
            }

            $this->getEntityManager()->persist($machine);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $machine;
    }

    /**
     * @Given /^there are following games:$/
     */
    public function thereAreFollowingGames(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsGame(
                $data['name'],
                $data['installName'],
                isset($data['launchName']) ? $data['launchName'] : $data['installName'],
                $data['bin'],
                $data['type'],
                (isset($data['available']) && $data['available'] == 'yes'),
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsGame($name, $installName = null, $launchName = null, $bin = null, $type = null, $available = true, $flush = true)
    {
        if (null === $game = $this->getRepository('game')->findOneBy(array('name' => $name))) {
            $game = $this->getRepository('game')->createNew();
            $game->setName($name);
            $game->setInstallName($installName);
            $game->setLaunchName($launchName);
            $game->setBin($bin);
            $game->setType($type);
            $game->setAvailable($available);

            $this->validate($game);

            $this->getEntityManager()->persist($game);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $game;
    }

    /**
     * @Given /^there are following plugins:$/
     */
    public function thereAreFollowingPlugins(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsPlugin(
                $data['name'],
                $data['version'],
                $data['scriptName'],
                'http://' . $data['downloadUrl'],
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsPlugin($name, $version, $scriptName, $downloadUrl, $flush = true)
    {
        if (null === $plugin = $this->getRepository('plugin')->findOneBy(array('name' => $name))) {
            $plugin = $this->getRepository('plugin')->createNew();
            $plugin->setName($name);
            $plugin->setVersion($version);
            $plugin->setScriptName($scriptName);
            $plugin->setDownloadUrl($downloadUrl);

            $this->validate($plugin);

            $this->getEntityManager()->persist($plugin);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $plugin;
    }

    /**
     * @Given /^there are following minecraft servers:$/
     */
    public function thereAreMinecraftServers(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsMinecraftServer(
                $data['name'],
                $data['machine'],
                $data['port'],
                $data['queryPort'],
                $data['rconPort'],
                $data['rconPassword'],
                $data['game'],
                $data['installDir'],
                $data['maxplayers'],
                $data['minHeap'],
                $data['maxHeap'],
                (isset($data['installed']) && $data['installed'] == 'yes'),
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsMinecraftServer($name, $machine = null, $port = 25565, $queryPort = 25565, $rconPort = 25575, $rconPassword = 'test', $game = 'minecraft', $installDir = 'test', $maxplayers = 2, $minHeap = 128, $maxHeap = 256, $installed = true, $flush = true)
    {
        if (null === $server = $this->getRepository('minecraft')->findOneBy(array('name' => $name))) {
            $game    = $this->thereIsGame($game);
            $machine = $this->thereIsMachine($machine);

            $server = new MinecraftServer();
            $server->setName($name);
            $server->setMachine($machine);
            $server->setPort($port);
            $server->setQueryPort($queryPort);
            $server->setRconPort($rconPort);
            $server->setRconPassword($rconPassword);
            $server->setGame($game);
            $server->setDir($installDir);
            $server->setMaxplayers($maxplayers);
            $server->setMinHeap($minHeap);
            $server->setMaxHeap($maxHeap);

            if ($installed) {
                $server->setInstallationStatus(101);
            }

            $this->validate($server);

            $this->getEntityManager()->persist($server);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $server;
    }

    /**
     * @Given /^there are following steam servers:$/
     */
    public function thereAreSteamServers(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsSteamServer(
                $data['name'],
                $data['machine'],
                $data['port'],
                $data['rconPassword'],
                $data['game'],
                $data['installDir'],
                $data['maxplayers'],
                (isset($data['installed']) && $data['installed'] == 'yes'),
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsSteamServer($name, $machine = null, $port = 27025, $rconPassword = 'test', $game = 'Counter-Strike', $installDir = 'test', $maxplayers = 2, $installed = true, $flush = true)
    {
        if (null === $server = $this->getRepository('steam')->findOneBy(array('name' => $name))) {
            $game    = $this->thereIsGame($game);
            $machine = $this->thereIsMachine($machine);

            $server = new SteamServer();
            $server->setName($name);
            $server->setMachine($machine);
            $server->setPort($port);
            $server->setRconPassword($rconPassword);
            $server->setGame($game);
            $server->setDir($installDir);
            $server->setMaxplayers($maxplayers);

            if ($installed) {
                $server->setInstallationStatus(101);
            }

            $this->validate($server);

            $this->getEntityManager()->persist($server);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $server;
    }

    /**
     * @Given /^there are following teamspeak servers:$/
     */
    public function thereAreTeamspeakServers(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsTeamspeakServer(
                $data['machine'],
                $data['queryPassword'],
                $data['installDir'],
                (isset($data['installed']) && $data['installed'] == 'yes'),
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsTeamspeakServer($machine, $queryPassword = 'test', $installDir = 'test', $installed = true, $flush = true)
    {
        $machine = $this->thereIsMachine($machine);

        if (null === $server = $this->getRepository('teamspeak')->findOneBy(array('machine' => $machine))) {
            $server = new TeamspeakServer();
            $server->setMachine($machine);
            $server->setQueryPassword($queryPassword);
            $server->setDir($installDir);

            if ($installed) {
                $server->setInstallationStatus(101);
            }

            $this->validate($server);

            $this->getEntityManager()->persist($server);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $server;
    }

    /**
     * @Given /^there are following teamspeak instances:$/
     */
    public function thereAreTeamspeakInstances(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsTeamspeakInstance(
                $data['instanceId'],
                $data['name'],
                $data['server'],
                $data['port'],
                $data['slots'],
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    public function thereIsTeamspeakInstance($instanceId, $name = 'Test', $server = 'test4@127.0.0.1', $port = 9887, $slots = 2, $flush = true)
    {
        $parts   = explode('@', $server);
        $machine = $this->findOneBy('machine', array('username' => $parts[0]));
        $server  = $this->findOneBy('teamspeak', array('machine' => $machine->getId()));

        if (null === $instance = $this->getRepository('instance', 'teamspeak')->findOneBy(array('server' => $server->getId()))) {
            $instance = new TeamspeakServerInstance($server);
            $instance->setInstanceId($instanceId);
            $instance->setName($name);
            $instance->setPort($port);
            $instance->setMaxClients($slots);
            $instance->setAdminToken('test');
            $instance->setAutostart(true);

            $this->validate($server);

            $this->getEntityManager()->persist($server);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $instance;
    }

    /**
     * @param string $baseName
     */
    protected function getRepository($resource, $baseName = null)
    {
        $service = 'dedipanel.';

        if (!empty($baseName)) {
            $service .= $baseName . '.';
        }

        return $this->getService($service . 'repository.'.$resource);
    }

    protected function findOneBy($type, array $criteria, $repoPrefix = '')
    {
        $resource = $this
            ->getRepository($type, $repoPrefix)
            ->findOneBy($criteria);

        if (null === $resource) {
            throw new \InvalidArgumentException(
                sprintf('%s for criteria "%s" was not found.', str_replace('_', ' ', ucfirst($type)), serialize($criteria))
            );
        }

        return $resource;
    }

    protected function validate($data)
    {
        $violationList = $this->getService('validator')->validate($data);

        if ($violationList->count() != 0) {
            throw new \RuntimeException(sprintf('Data not valid (%s).', $violationList));
        }
    }
}
