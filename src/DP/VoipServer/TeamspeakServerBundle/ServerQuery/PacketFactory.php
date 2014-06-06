<?php

namespace DP\VoipServer\TeamspeakServerBundle\ServerQuery;

use DP\Core\CoreBundle\Service\PacketFactory as BasePacketFactory;

class PacketFactory extends BasePacketFactory
{
    public function getLoginPacket($login, $pass)
    {
        return self::newPacket($this->escapePacket('login ' . $login . ' ' . $pass));
    }

    public function escapePacket($packet)
    {
        $symbols = $this->getEscapeSymbols();
        $symbols = array_reverse($symbols);

        $search = array_map(function ($el) {
            return $el[1];
        }, $symbols);
        $replace = array_map(function ($el) {
            return $el[0];
        }, $symbols);

        return str_replace($search, $replace, $packet);
    }

    public function unescapePacket($packet)
    {
        $symbols = $this->getEscapeSymbols();

        $search = array_map(function ($el) {
            return $el[0];
        }, $symbols);
        $replace = array_map(function ($el) {
            return $el[1];
        }, $symbols);

        return str_replace($search, $replace, $packet);
    }

    /**
     * Return all symbols that need escaping
     * The array is provide in the "unescape" way,
     * so it need to be reverse, and subarray values too
     *
     * @return array
     */
    private function getEscapeSymbols()
    {
        return array(
            array('\v', "\v"),
            array('\t', "\t"),
            array('\r', "\r"),
            array('\n', "\n"),
            array('\f', "\f"),
            array('\b', chr(8)),
            array('\a', ""),
            array('\p', '|'),
            array('\s', ' '),
            array('\/', '/'),
            array('\\\\', '\\'),
        );
    }
}
