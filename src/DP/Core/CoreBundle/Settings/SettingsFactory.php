<?php

namespace DP\Core\CoreBundle\Settings;

class SettingsFactory
{
    /**
     * @var SettingsReader
     */
    private $reader;

    /**
     * @param SettingsReader $reader
     */
    public function __construct(SettingsReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @return Settings
     */
    public function create()
    {
        $settings = $this->reader->read();

        return new Settings(isset($settings['debug']) ? $settings['debug'] : false);
    }
}
