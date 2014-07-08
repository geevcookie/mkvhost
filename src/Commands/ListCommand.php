<?php

namespace Mkvhost\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists all the existing virtual hosts.
 *
 * Class ListCommand
 * @package Mkvhost\Commands
 */
class ListCommand extends AbstractCommand
{
    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this->setName('list');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (glob($this->config['virtualHostPath'].'/*.conf') as $file) {
            $output->writeln($file);
        }
    }
}