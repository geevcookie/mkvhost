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
        return json_decode(file_get_contents($this->getFilePath()), true);
    }

    /**
     * @param string $server
     * @param string $vhostPath
     * @param string $rootPath
     * @return bool
     */
    public function createConfig($server, $vhostPath, $rootPath)
    {
        $config = <<<EOT
{
    "server": "$server",
    "virtualHostPath": "$vhostPath",
    "rootPath": "$rootPath",
    "defaults": {
        "options": "All",
        "allowOverride": "All",
        "order": "deny,allow",
        "deny": "all",
        "allow": "127.0.0.1",
        "directoryIndex": "index.php index.html"
    }
}
EOT;

        @file_put_contents($this->getFilePath(), $config);
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
