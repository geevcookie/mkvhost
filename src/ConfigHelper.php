<?php

namespace Mkvhost;

use Symfony\Component\Console\Output\OutputInterface;

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
     * Ensures that the config file is in place.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Exception
     * @return bool
     */
    public function checkConfig(OutputInterface $output)
    {
        if (!file_exists($this->getFilePath())) {
            $output->writeln('<info>Config file does not exist. Trying to create in:</info> '.$this->getFilePath());

            if (!$this->createConfig($output)) {
                throw new \Exception('Could not create default config file in: ' . $this->getFilePath());
            } else {
                $output->writeln('<question>Config file created!</question>');
            }
        } else {
            $output->writeln('<info>Using existing config file:</info> '.$this->getFilePath());
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
    "virtualHostPath": "/etc/apache2/virtualhosts",
    "rootPath": "",
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