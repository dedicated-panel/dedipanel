<?php

namespace DP\Core\CoreBundle\Settings;

use Symfony\Component\Yaml\Yaml;

class SettingsWriter
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @param Settings $settings
     *
     * @return bool
     */
    public function write(Settings $settings)
    {
        $yaml = Yaml::dump($this->convertToArray($settings), 2);

        return (bool) file_put_contents($this->filePath, $yaml);
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return is_writable($this->filePath);
    }

    /**
     * @param Settings $settings
     *
     * @return array
     */
    private function convertToArray(Settings $settings)
    {
        return [
            'debug' => (bool) $settings->getDebug(),
        ];
    }
}
