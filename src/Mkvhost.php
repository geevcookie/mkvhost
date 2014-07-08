<?php namespace Mkvhost;

use Mkvhost\Commands\MkvhostCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class Mkvhost
 * @package Mkvhost
 */
class Mkvhost extends Application
{
    /**
     * @param InputInterface $input
     * @return string
     */
    protected function getCommandName(InputInterface $input)
    {
        return "mkvhost";
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new MkvhostCommand();

        return $defaultCommands;
    }

    /**
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}