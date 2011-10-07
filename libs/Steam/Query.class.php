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

require_once(SOCK_DIR . 'Socket.class.php');

abstract class Query extends SocketUDP {
	// Cette méthode est implémenté séparement dans les classes filles GoldSrcQuery et SourceQuery puisque les réponses à la requête de ping est différente
    abstract protected function latency();
	// De même pour cette méthode, les infos sur le serveur ne sont pas renvoyées de la même manière selon le type de serveur
    abstract protected function recupInfos();

    // Renvoie la latence en ms
    public function getLatency() {
        // On vérfie que la latence a été récupéré, sinon on la récupère
        // En cas d'erreur, la fonction renvoie false
        $verify = $this->verifyLatency();

        // On renvoie false si la fonction a renvoié false si on n'a pas pu récupérer la latence
        if ($verify === false) {
            return false;
        }

        return $this->latency;
    }
    public function isOnline() {
        // On vérfie que la latence a été récupéré, sinon on la récupère
        // En cas d'erreur, la fonction renvoie false
        $verify = $this->verifyLatency();

		// On renvoie false si on n'a pas pu récupérer la latence, sinon true
		return ($verify === false) ? false : true;
    }
	// Renvoie les infos sur le serveur (les récupère si nécessaire)
    public function getInfos() {
        if (!isset($this->infos)) {
            $this->recupInfos();
        }

        return $this->infos;
    }
    // Récupère les infos sur les joueurs et traite la réponse (si ça n'a pas été fait)
	// Renvoie un résultat sous forme d'array. L'index 0 correspond à l'en-tête de la réponse, la liste des joueurs commence à l'index 1
	public function getPlayers() {
        if (!isset($this->players)) {
            // On récupère le challenge
            $challenge = $this->getChallenge();
            $rep = $this->A2S_PLAYER($challenge);
            $players = array();

            $vars = array(
                'header' => 'byte',
                'nb_players' => 'byte',
            );
            $players[0] = Packet::extractPacket($rep, $vars);

            for ($i = 0, $max = $players[0]['nb_players']; $i < $max; ++$i) {
                $vars = array(
                    'id' => 'byte',
                    'nom' => 'string',
                    'score' => 'long',
                    'temps_connexion' => 'float',
                );

                $players[$i+1] = Packet::extractPacket($rep, $vars);
            }

            $this->players = $players;
        }

        return $this->players;
    }
	// Récupère les cvars publiques et traite la réponse brute du serveur
	// Renvoie un résultat sous forme d'array. L'index 0 correspond à l'en-tête de la réponse. La liste des cvars commence à l'index 1
    public function getRules() {
        if (!isset($this->rules)) {
            $challenge = $this->getChallenge();
            $rep = $this->A2S_RULES($challenge);
            $rules = array();

            $vars = array(
                'header' => 'byte',
                'nb_rules' => 'short',
            );
            $rules[0] = Packet::extractPacket($rep, $vars);

            if ($rules[0]['header'] == 69) {
                for($i = 0; $i < $rules[0]['nb_rules']; ++$i) {
                    $vars = array(
                        'nom' => 'string',
                        'val' => 'string',
                    );

                    $rules[$i+1] = Packet::extractPacket($rep, $vars);
                }
            }

            $this->rules = $rules;
        }

        return $this->rules;
    }


	// Cette méthode envoie la requête de ping
	// Elle renvoie le résultat brut de la réponse au ping ainsi que la latence
    protected function A2A_PING() {
        $latency = microtime(true);

        $this->send(self::A2A_PING);
        $ping = $this->recv();

        // On met la latence en ms (* 1000)
        $latency = round((microtime(true) - $latency) * 1000);

        if ($ping == null) {
            $latency = -1;
        }

        return array('rep' => $ping, 'latency' => $latency);
    }
    // Vérifie si la latence a été récupéré, la récupère si nécessaire
	// La fonction renvoie fale si la latence n'est pas pu être récupéré ou si le serveur est offline
	protected function verifyLatency() {
        if (!isset($this->latency)) {
			$this->latency();
        }
        if ($this->latency == -1 || $this->connected === false) {
            return false;
        }

        return true;
    }

	// Cette méthode envoie une requête pour récupérer les infos concernant le serveur
	// Elle renvoie le résultat brut de la requête
    protected function A2S_INFO() {
        $this->send(self::A2S_INFO);
        $infos = $this->recv();
        return $infos;
    }

	// Cette méthode renvoie le challenge acquis auprès du serveur
    protected function getChallenge() {
        if (!isset($this->challenge)) {
            $rep = $this->A2S_PLAYER("\xFF\xFF\xFF\xFF");

            // On prépare la liste des variables à extraires
            $vars = array(
                'header' => 'byte',
                'challenge' => 'long',
            );
            
            $infos = Packet::extractPacket($rep, $vars);

            if ($infos['header'] == 65) {
                $this->challenge = $infos['challenge'];
            }
        }

        return $this->challenge;
    }
	
	// Cette méthode envoie une requête pour récupérer les infos sur les joueurs connectés au serveur
	// Elle nécessite un challenge spécifié à la fonction. Si celui-ci est nul, la requête renvoie un challenge
	// Sinon, elle renvoie les données bruts.
    protected function A2S_PLAYER($challenge) {
        $req = self::A2S_PLAYER;
        Packet::addLong($req, $challenge);

        $this->send($req);
        $rep = $this->recv();

        return $rep;
    }

	// Cette méthode envoie une requête pour récupérer les cvars publiques du serveur
	// Elle nécessite un challenge spécifié à la fonction. Elle renvoie le résultat brut de la requête.
    protected function A2S_RULES($challenge) {
        $req = self::A2S_RULES;
        Packet::addLong($req, $challenge);

        $this->send($req);
        $rep = $this->recv();

        return $rep;
    }

    // Cette méthode permet d'envoyer des données au serveur
    // Elle ajoute le header à la requête
    protected function send($data)  {
        if ($this->connected === false) {
            return;
            trigger_error('Query::send call when socket is disconnected.');
        }

        $data = self::Q_HEADER . $data;

        parent::send($data, strlen($data));
    }

    //abstract protected function recvMultiPackets($packet, $length = MTU);
    // Cette méthode permet de réamssbler des packets reçu en fragments
    public static function reassemblePackets($split_packets, $compressed = false, $checksum = 0) {
        $data = '';

        if ($split_packets === false) {
            return false;
        }

        foreach ($split_packets AS $packet) {
            if ($packet == null) {
                trigger_error('Query::reassemblePackets uncomplete split_packets.', E_USER_ERROR);
            }

            $data .= $packet;
        }

        if ($compressed) {
            $data = bzdecompress($data);
        }

        if ($checksum != 0) {
            if (crc32($data) != $checksum) {
                trigger_error('Query::reassemblePackets checksums not match.');
            }
        }
        
        // On enlève le premier entier permettant de déterminer s'il s'agit d'une réponse multi paquet
        Packet::getLong($data);

        return $data;
    }
    // Permet de savoir si une réponse est scindée en plusieurs paquets
    protected function isMultiPacketResp($packet) {
        return Packet::getLong($packet) == -2;
    }

    const Q_HEADER = "\xFF\xFF\xFF\xFF"; //0xFFFFFFFF;
    const A2A_PING = "\x69"; //0x69;
    const A2S_INFO = "TSource Engine Query\0";
    const A2S_PLAYER = "\x55"; //0x55;
    const A2S_RULES = "\x56"; //0x56;

    protected $challenge;
    protected $latency;
    protected $infos;
    protected $players;
    protected $rules;
}

class GoldSrcQuery extends Query {
    protected function recv() {
        $packets = parent::recv();

        if (!$packets) return null;
        
        $data = Query::reassemblePackets($packets);

        return $data;
    }

    // Permet de récupérer une réponse fragmentée en plusieurs paquets
	protected function recvMultiPackets($packet, $length = MTU) {
        $split_packets = array();

        do {
            $header = Packet::getLong($packet);
            $id = Packet::getLong($packet);

            // On récupère le nbre de paquet puis son numéro
            $packet_infos = Packet::getByte($packet);
            $nbre_packet = $packet_infos & 0xF;
            $id_packet = $packet_infos >> 4;

            $split_packets[$id_packet] = $packet;

            $split_packets[$id_packet] = $packet;

            $packet = null;
            if (count($split_packets) < $nbre_packet) {
                $packet = parent::recv(false, $length);
            }
        } while($packet && $this->isMultiPacketResp ($packet));

        return $split_packets;
    }

	// Envoie une requête A2A_PING pour récupérer la latence
	// On vérifie que le serveur renvoie une réponse ayant un format correct
	// Sinon, on délcare le serveur comme offline
    protected function latency() {
        // On récupère le ping et la réponse à la requête
        $ping = $this->A2A_PING();

        if ($ping['rep'] !== false) {
            $vars = array('header' => 'byte', 'body' => 'string');
            $ping['rep'] = Packet::extractPacket($ping['rep'], $vars);

            // Si la réponse n'est pas bonne, on met le ping à -1 et on déclare le serveur comme étant offline
            if ($ping['rep']['header'] != 106 || !empty($ping['rep']['body'])) {
                $this->connected = false;
                $this->latency = -1;
            }
            else {
                $this->latency = $ping['latency'];
            }
        }
    }

    protected function recupInfos() {
        $rep = $this->A2S_INFO();

        // On prépare la liste des variables à extraires
        $vars = array(
            'header' => 'byte',
            'protocol' => 'byte',
            'server_name' => 'string',
            'map' => 'string',
            'game_dir' => 'string',
            'game_name' => 'string',
            'app_id' => 'short',
            'nb_players' => 'byte',
            'nb_max_players' => 'byte',
            'nb_bot' => 'byte',
            'srv_type' => 'byte',
            'os' => 'byte',
            'password' => 'byte',
            'vac' => 'byte',
            'game_ver' => 'string',
            'edf' => 'byte'
        );

        $infos = Packet::extractPacket($rep, $vars);

        if ($infos['edf'] & 0x80) {
           $infos['port'] = Packet::getShort($rep);
        }
        elseif ($infos['edf'] & 0x40) {
            $infos['spec_port'] = Packet::getShort($rep);
            $infos['spec_name'] = Packet::getString($rep);
        }
        elseif ($infos['edf'] & 0x20) {
            $infos['keywords'] = Packet::getString($rep);
        }

        $this->infos = $infos;
    }
}

class SourceQuery extends Query {
    protected function recv() {
        $packets = parent::recv();
        if (!$packets) return null;
        
        $data = Query::reassemblePackets($packets);
        
        return $data;
    }
    protected function recvMultiPackets($packet, $length = MTU) {
        $split_packets = array();
        $respId = null;

        do {
            // On retire l'header indiquant qu'on a une réponse morcellé
            $header = Packet::getLong($packet);

            // Puis on récupère l'id de la réponse et on vérifie qu'elle corresponde bien au paquet
            // Qu'on récupère
            $id = Packet::getLong($packet);
            if (!$respId) {
                $respId = $id;
            }
            elseif ($respId != $id) {
                continue;
            }

            // On récupère le nombre de paquet puis son numéro
            $packet_infos = Packet::getByte($packet);
            $nbre_packet = $packet_infos & 0xF;
            $id_packet = $packet_infos >> 4;

            $split_packets[$id_packet] = $packet;

            $packet = null;
            // On vérifie si on a reçu toutes les données
            // Si ce n'est pas le cas, on récupère un nouveau paquet
            if (count($split_packets) < $nbre_packet) {
                $packet = parent::recv(false, $length);
            }
        } while($packet && $this->isMultiPacketResp($packet));

        return $split_packets;
    }

    protected function latency() {
        // On récupère le ping et la réponse à la requête
        /*$ping = $this->A2A_PING();
        $vars = array('header' => 'byte', 'body' => 'string');
        $ping['rep'] = Packet::extractPacket($ping['rep'], $vars);

        // Si la réponse n'est pas la bonne, on met le ping à -1
        if ($ping['rep'] === false || $ping['rep']['header'] != 106 || $ping['rep']['body'] != "00000000000000") {
            $this->connected = false;
            $this->latency = -1;
        }
        else {
            $this->latency = $ping['latency'];
        }*/

        $this->send(self::A2S_INFO);

        $temps = microtime(true);
        $infos = $this->recv();
        $temps = round((microtime(true) - $temps) * 1000);

        if ($infos == NULL) {
            $this->connected = false;
            $this->latency = -1;
        }
        else {
            $this->latency = $temps;
        }
    }

    protected function recupInfos() {
        $rep = $this->A2S_INFO();

        // On prépare la liste des variables à extraires
        $vars = array(
            'header' => 'byte',
            'protocol' => 'byte',
            'server_name' => 'string',
            'map' => 'string',
            'game_dir' => 'string',
            'game_name' => 'string',
            'app_id' => 'short',
            'nb_players' => 'byte',
            'nb_max_players' => 'byte',
            'nb_bot' => 'byte',
            'srv_type' => 'byte',
            'os' => 'byte',
            'password' => 'byte',
            'vac' => 'byte',
            'game_ver' => 'string',
            'edf' => 'byte'
        );

        $infos = Packet::extractPacket($rep, $vars);

        if ($infos['edf'] & 0x80) {
           $infos['port'] = Packet::getShort($rep);
        }
        elseif ($infos['edf'] & 0x40) {
            $infos['spec_port'] = Packet::getShort($rep);
            $infos['spec_name'] = Packet::getString($rep);
        }
        elseif ($infos['edf'] & 0x20) {
            $infos['keywords'] = Packet::getString($rep);
        }

        $this->infos = $infos;
    }
}
?>