<?php

namespace Mkvhost\Commands;

use Exception;
use Mkvhost\ConfigHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class ConfigCommand
 * @package Mkvhost\Commands
 */
class ConfigCommand extends Command
{
    /**
     * Configures the command.
     */
    public function configure()
    {
        $this->setName('config');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->getFilePath())) {
            $output->writeln(
                "<info>Its the first time you are running mkvhost. Let's set up a config file for you.</info>"
            );

            // Set up the config file helper.
            $helper = new ConfigHelper();

            // Get the details.
            $server    = $this->getServer($input, $output);
            $vhostPath = $this->getVhostPath($input, $output, $server);
            $rootPath  = $this->getRootPath($input, $output);

            // Try to save the config file.
            try {
                $helper->createConfig($server, $vhostPath, $rootPath);
                $output->writeln('<info>Config file successfully created!');
            } catch (Exception $e) {
                $output->writeln('<error>Could not write config file!</error>');
            }
        } else {
            $output->writeln('Using config file: ' . $this->getFilePath());
        }
    }

    /**
     * Helper function to get the path to the config file.
     *
     * @return string
     */
    private function getFilePath()
    {
        return getenv('HOME') . '/.mkvhost';
    }

    /**
     * Asks the user which server is being used.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function getServer(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Are you using Apache (default) or Nginx?',
            array('apache', 'nginx'),
            0
        );

        return $helper->ask($input, $output, $question);
    }

    /**
     * Gets the path to the vhost directory.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $server
     * @return mixed
     */
    public function getVhostPath(InputInterface $input, OutputInterface $output, $server)
    {
        $default  = "/etc/$server/vhosts";
        $helper   = $this->getHelper('question');
        $question = new Question("Where would you like the vhosts to be stored? (Default: $default) ", $default);

        return $helper->ask($input, $output, $question);
    }

    /**
     * Gets the path to the project directory.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function getRootPath(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new Question('What is the root path to your projects? ', '');
        $question->setValidator(
            function ($answer) {
                if ($answer == '') {
                    throw new \RuntimeException(
                        'The path can not be blank!'
                    );
                }

                return $answer;
            }
        );

        return $helper->ask($input, $output, $question);
    }
}
