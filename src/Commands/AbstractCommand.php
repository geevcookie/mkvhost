<?php

namespace Mkvhost\Commands;

use Mkvhost\ConfigHelper;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param null $name
     */
    public function __construct($name = null)
    {
        parent::__construct();

        // Get the config.
        $configHelper = new ConfigHelper();
        $this->config = $configHelper->getConfig();
    }
}