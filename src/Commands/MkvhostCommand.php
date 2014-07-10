<?php

namespace Mkvhost\Commands;

use Mkvhost\ConfigHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The main command. Acts like a bridge and simply calls other existing commands.
 *
 * Class MkvhostCommand
 * @package Mkvhost\Commands
 */
class MkvhostCommand extends Command
{
    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName('mkvhost')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The server name to use for the virtual host.'
            )
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'Lists the existing virtual hosts.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name   = $input->getArgument('name');
        $helper = new ConfigHelper();

        if ($helper->checkConfig($output)) {
            // Add the hidden commands.
            $this->getApplication()->add(new ListCommand());
            $this->getApplication()->add(new MakeCommand());

            // Process the command.
            if ($input->getOption('list')) {
                $command = $this->getApplication()->find('list');
                $command->run(new ArrayInput(array()), $output);
            } else {
                if ($name) {
                    $command = $this->getApplication()->find('make');
                    $command->run(new ArrayInput(array('name' => $name)), $output);
                } else {
                    $output->writeln('No name specified');
                }
            }
        } else {
            $output->writeln('<error>Could not initialize config!</error>');
        }
    }
}