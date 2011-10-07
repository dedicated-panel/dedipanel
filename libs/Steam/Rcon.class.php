<?php
/*
** Copyright (C) 2010-2011 Kerouanton Albin, Smedts Jérôme
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

// MTU Steam
define('MTU', 4096);

require_once(SOCK_DIR . 'Socket.class.php');

class GoldSrcRcon extends SocketUDP {
    public function __construct($ip, $port, $mdp, $ip6 = false) {
        $this->mdp = $mdp;
        parent::__construct($ip, $port, $ip6);

        $this->getChallenge();
    }

    // Cette méthode permet de récupérer le challenge attribué par le serveur
    // Et permet également de construire l'en-tête d'une requête rcon
    private function getChallenge() {
        $packet = self::RCON_HEADER . self::RCON_GETCHALLENGE;
        $len = strlen($packet);
        parent::send($packet, $len);

        // 'challenge rcon ' => 15 caracts
        $ret = $this->recv(false);
        Packet::getLong($ret); // On enlève l'en-tête de la réponse
        $challenge = substr($ret, 15, strlen($ret) - 17); // 15 + 2 caracts à la fin

        $this->header = self::RCON_HEADER . self::RCON_EXECMD . $challenge . ' "' . $this->mdp . '" ';
    }

    public function execCmd($cmd) {
        if (!isset($this->header)) {
            trigger_error('Le challenge n\'a pas encore été récupéré.');
        }

        $packet = $this->header . $cmd;
        $len = strlen($packet);
        parent::send($packet, $len);

        // On récupère le paquet et on rassemble les données
        $recv = $this->recv();
        $ret = $recv;
        //$ret = $this->reassemblePackets($recv);

        // On enlève l'en tête de 4 octets
        $l = Packet::getLong($ret);
        $b = Packet::getByte($ret);  // ?
        
        $ret = substr($ret, 0, -2);
        return $ret;
    }

    protected function recv($multipacket = true, $length = MTU) {
        $packets = '';

        // On boucle jusqu'a ne plus recevoir de données
        do {
            $packet = parent::recv(false, $length);
            if ($packet != false) {
                $packets .= $packet;
            }
        } while ($packet != false);
        
        return $packets;
    }

    protected function isMultiPacketResp($packet) { 
        return false;
    }
    protected function recvMultiPackets($packet, $length = MTU) {
        return $packet;
    }
    private function reassemblePackets($split_packets) {
        if (count($split_packets) == 1) {
            return $split_packets[0];
        }

        return implode($split_packets);
    }

    private $header;
    private $mdp;

    const RCON_HEADER = "\xFF\xFF\xFF\xFF";
    const RCON_GETCHALLENGE = 'challenge rcon';
    const RCON_EXECMD = 'rcon ';
}
class SourceRcon extends SocketTCP {
    public function __construct($ip, $port, $mdp, $ip6 = false) {
        $this->mdp = $mdp;

        parent::__construct($ip, $port, $ip6);

        $authed = $this->auth();
    }

    protected function send($cmd, $type = self::SERVERDATA_EXECOMMAND) {
        $id = rand(0, pow(2, 16));
        $this->id = $id;

        $data = Packet::Long($id) . Packet::Long($type) . $cmd . chr(0) . '' . chr(0);
        $len = strlen($data);

        $data = Packet::Long($len) . $data;
        $len += 4;

        parent::send($data, $len);
    }

    private function auth() {
        $this->send($this->mdp, self::SERVERDATA_AUTH);

        // Le serveur renvoie un premier paquet qu'est inutile puis un second
        $ret = $this->recv(false);

        if ($ret[1]['type'] != self::SERVERDATA_AUTH_RESPONSE) {
            $this->authed = false;
            $this->disconnect();
            throw new Exception('Mauvais RCON.');
        }

        $this->authed = true;

        return true;
    }

    public function execCmd($cmd) {
        if (!$this->authed) return false;
        $this->send($cmd, self::SERVERDATA_EXECOMMAND);
        $ret = $this->recv();

        if ($ret['id'] != $this->id || $ret['type'] != self::SERVERDATA_RESPONSE_VALUE) {
            echo 'id || type err !';
            return false;
        }
        elseif (strlen($ret['s1']) != $ret['len']) {
            echo 'len fail !';
            return false;
        }

        return $ret['s1'];
    }

    /*protected function recv() {
        $resp = null;
        $splitted = false;

        // On récupère la taille du premier paquet
        $octetsRestatns = Packet::getLong(parent::recv(false, 4));

        do {
            // On récupère le nbre d'octets souhaités ($octetsRestants)
            $ret = parent::recv(false, $octetsRestants);

            // Si le premier paquet de la répoonse à été récupéré
            // On s'occupe uniquement d'ajouter les données à celles du premier paquet
            if(isset($resp)) {
                // S'il ne s'agit pas d'un paquet
                if (!$splitted) {

                }
                else {

                }
            }
            else {
                $resp = $this->getData($ret);
            }
        } while ($octetsRestants > 0);
    }*/
    protected function recv($multipacket = true) {
        $resp = null;

        // On récupère la taille du premier paquet
        $octetsRestants = Packet::getLong(parent::recv(false, 4));

        do {
            $ret = parent::recv($multipacket, $octetsRestants);

            if ($multipacket) {
                // Etant donné qu'on attend des réponses multipackets, la fonction recv renvoie un array
                // On lee transforme donc en chaîne
                $ret = $this->reassemblePackets($ret);
                
                // On ajoute les données du paquet reçue à celles du premier paquet
                // Si celles-ci n'ont pas encore été récupéré, on s'en occupe
                if (isset($resp)) {
                    // On récupère les données, on supprime donc les 8 premiers octets (int id, int type)
                    // Ainsi que les deux derniers (2x \0)
                    $ret = substr($ret, 8, -2);
                    $resp['s1'] .= $ret;
                }
                else {
                    $resp = $this->getData($ret);
                    $resp['len'] = 0;
                }

                // On enlève les 10 octets entourant les données transmises dans la réponse
                // Et on ajoute ce nombre à la longueur de la réponse
                $octetsRestants -= 10;
                $resp['len'] += $octetsRestants;
            }
            else {
                $resp[] = $this->getData($ret);
            }

            // On récupère la longueur du prochain paquet
            $octetsRestants = Packet::getInt(parent::recv(false, 4));
        } while ($octetsRestants > 0);
        
        return $resp;
    }
    protected function isMultiPacketResp($packet, $len) {
        return strlen($packet) < $len;
    }
    protected function recvMultiPackets($packet, $length = MTU) {
        $packets = $packet;

        // On calcule le nombre d'octets restants à récupérer
        $octetsRestants = $length - strlen($packet);

        do {
            // On récupère les données restantes et on les ajoutes au paquet actuel
            $packet = parent::recv(false, $octetsRestants);
            $packets .= $packet;

            // On recalcule le nombre d'octets restants
            // Pour s'assurer que toutes les données on été récupéré
            $octetsRestants = $length - strlen($packets);
        } while($octetsRestants > 0);
        
        return array($packets);
    }
    private function reassemblePackets($split_packets) {
        if (count($split_packets) == 1) {
            return $split_packets[0];
        }

        return implode($split_packets);
    }
    private function getData($packet) {
        $id = Packet::getInt($packet);
        if ($id != $this->id) return false;
        
        return array(
            'id' => $id, 
            'type' => Packet::getInt($packet),
            's1' => Packet::getStr($packet));
    }

    const SERVERDATA_AUTH = 3;
    const SERVERDATA_AUTH_RESPONSE = 2;
    const SERVERDATA_EXECOMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;

    private $mdp;
    private $id = 0;
    private $authed;
}


/*echo '<h1>Status</h1>';

$copa = new RconSource('188.165.249.132', 31000, 'redbull');
$status = $copa->execCmd('status');
print_array($status);

$ng = new RconGoldSrc('94.23.193.191', 27015, 'NG-WAR-PrAcC');
$status = $ng->execCmd('status');
print_array($status);*/

/*echo '<h1>CVAR List</h1>';

$copa = new RconSource('188.165.249.132', 31000, 'redbull');
$cvarlist = $copa->execCmd('cvarlist');
print_array($cvarlist);*/

//$ng = new RconGoldSrc('94.23.193.191', '27015', 'NG-WAR-PrAcC');

/*echo '<h1>Status</h1>';
$status = $ng->execCmd('status');
print_array($status);*/

/*echo '<h1>CVAR List</h1>';
$cvarlist = $ng->execCmd('cvarlist');
print_array($cvarlist);*/
?>