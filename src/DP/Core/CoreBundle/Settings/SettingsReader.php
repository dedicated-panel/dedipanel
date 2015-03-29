<?php

namespace DP\Core\CoreBundle\Settings;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class SettingsReader
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param string $filePath
     * @param LoggerInterface $logger
     */
    public function __construct($filePath, LoggerInterface $logger)
    {
        $this->filePath = $filePath;
        $this->logger   = $logger;
    }

    /**
     * @return array
     */
    public function read()
    {
        $settings = [];

        try {
            $settings = Yaml::parse($this->filePath);
        } catch (ParseException $e) {
            $this->logger->error(sprintf('Unable to read the core settings file "%s".', $this->filePath));
        }

        return $settings;
    }

    /**
     * @return bool
     */
    public function fileExists()
    {
        return file_exists($this->filePath);
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return is_readable($this->filePath);
    }
}
