<?php

namespace Mkvhost;

/**
 * Class ConfigHelper
 * @package Mkvhost
 */
class ConfigHelper
{
    /**
     * @return array
     */
    public function getConfig()
    {
        // Check if the config exists.
        $this->checkConfig();

        return json_decode(file_get_contents($this->getFilePath()), true);
    }

    /**
     * Ensures that the config file is in place.
     *
     * @return bool
     * @throws \Exception
     */
    public function checkConfig()
    {
        if (!file_exists($this->getFilePath()) && !$this->createConfig()) {
            throw new \Exception('Could not create default config file in: '.$this->getFilePath());
        }

        return true;
    }

    /**
     * Creates the default config file.
     *
     * @return bool
     */
    private function createConfig()
    {
        $config = <<<EOT
{
    "virtualHostPath": "/etc/apache2/virtualhosts"
}
EOT;

        if (file_put_contents($this->getFilePath(), $config)) {
            return true;
        }

        return false;
    }

    /**
     * Helper function to get the path to the config file.
     *
     * @return string
     */
    private function getFilePath()
    {
        return getenv('HOME').'/.mkvhost';
    }
}