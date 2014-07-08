<?php

namespace Mkvhost\Commands;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCommand extends AbstractCommand
{
    public function configure()
    {
        $this
            ->setName('make')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The server name to use for the virtual host.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');
        $name   = $input->getArgument('name');

        // First we want the document root of the virtual host.
        $defaultDocRoot    = $this->config['documentRoot'] . '/' . $name;
        $autocompleteParts = array();

        // Set up auto complete hints.
        foreach (explode('/', $defaultDocRoot) as $part) {
            if (count($autocompleteParts) > 0) {
                $autocompleteParts[] = $autocompleteParts[count($autocompleteParts) - 1] . $part . '/';
            } else {
                $autocompleteParts[] = '/';
            }
        }

        // Finally askt he user the questions.
        $documentRoot = $dialog->askAndValidate(
            $output,
            'Please enter the full path to the directory root (<info>' . $defaultDocRoot . '</info>): ',
            function ($answer) {
                if (substr($answer, 0, 1) !== '/') {
                    throw new \RuntimeException('Invalid path specified!');
                }

                return $answer;
            },
            false,
            $defaultDocRoot,
            $autocompleteParts
        );

        // Directory Options.
        $directoryOptions = $dialog->askAndValidate(
            $output,
            'Set the directory options (Options <info>All</info>): ',
            function ($answer) {
                if (strstr(strtolower($answer), 'options')) {
                    throw new \RuntimeException('\'Options\' keyword detected! This can be excluded.');
                }

                return $answer;
            },
            false,
            'All'
        );

        // Override Options
        $overrideOptions = $dialog->askAndValidate(
            $output,
            'Set the override options (AllowOverride <info>All</info>): ',
            function ($answer) {
                if (strstr(strtolower($answer), 'allowoverride')) {
                    throw new \RuntimeException('\'AllowOverride\' keyword detected! This can be excluded.');
                }

                return $answer;
            },
            false,
            'All'
        );

        // Order Directive.
        $orderDirective = $dialog->askAndValidate(
            $output,
            'Set the \'Order\' directive (Order <info>deny,allow</info>): ',
            function ($answer) {
                if (strstr(strtolower($answer), 'order')) {
                    throw new \RuntimeException('\'Order\' keyword detected! This can be excluded.');
                }

                return $answer;
            },
            false,
            'deny, allow',
            array('deny,allow', 'allow,deny')
        );

        // Deny Directive
        $denyDirective = $dialog->askAndValidate(
            $output,
            'Set the \'Deny\' directive (Deny from <info>all</info>): ',
            function ($answer) {
                if (strstr(strtolower($answer), 'deny')) {
                    throw new \RuntimeException('\'Deny\' keyword detected! This can be excluded.');
                }

                return $answer;
            },
            false,
            'all'
        );

        // Allow Directive
        $allowDirective = $dialog->askAndValidate(
            $output,
            'Set the \'Allow\' directive (Allow from <info>127.0.0.1</info>): ',
            function ($answer) {
                if (strstr(strtolower($answer), 'allow')) {
                    throw new \RuntimeException('\'Allow\' keyword detected! This can be excluded.');
                }

                return $answer;
            },
            false,
            '127.0.0.1'
        );

        // Directory Index
        $directoryIndex = $dialog->askAndValidate(
            $output,
            'Set the directory index (DirectoryIndex <info>index.php index.html</info>): ',
            function ($answer) {
                if (strstr(strtolower($answer), 'directoryindex')) {
                    throw new \RuntimeException('\'DirectoryIndex\' keyword detected! This can be excluded.');
                }

                return $answer;
            },
            false,
            'index.php index.html'
        );

        // Environment Variables
        $envVariables = array();
        do {
            $answer = $dialog->ask(
                $output,
                'Set the name of the environment variable (Leave blank to stop adding environment variables): ',
                ''
            );

            if ($answer != '') {
                $envVariables[$answer] = $dialog->askAndValidate(
                    $output,
                    'Enter the value of the environment variable - <info>' . $answer . '</info>: ',
                    function ($answer) {
                        if ($answer == '') {
                            throw new \RuntimeException('Value can not be blank!');
                        }

                        return $answer;
                    },
                    false,
                    ''
                );
            }
        } while ($answer != '');

        // Get the virtual host.
        $virtualHost = $this->getVirtualHost(
            $documentRoot,
            $name,
            $directoryOptions,
            $overrideOptions,
            $orderDirective,
            $denyDirective,
            $allowDirective,
            $directoryIndex,
            $this->generateEnvVariables($envVariables)
        );

        // Confirm virtual host.
        $dialog->askConfirmation(
            $output,
            "<info>$virtualHost</info>\r\n<question>Enter to continue, CTRL-C to cancel:</question> ",
            true
        );
    }

    /**
     * Generates the output for specified environment variables.
     *
     * @param array $variables
     * @return string
     */
    private function generateEnvVariables(array $variables)
    {
        $output = '';

        foreach ($variables as $name => $value) {
            $output .= "\r\n    SetEnv $name $value";
        }

        return ($output === '') ? '' : "\r\n$output";
    }

    /**
     * Generates the final output of the virtual host.
     *
     * @param string $path
     * @param string $domain
     * @param string $options
     * @param string $override
     * @param string $order
     * @param string $deny
     * @param string $allow
     * @param string $index
     * @param array  $envVars
     * @return string
     */
    private function getVirtualHost($path, $domain, $options, $override, $order, $deny, $allow, $index, $envVars)
    {
        return <<<EOT
<VirtualHost *:80>
    DocumentRoot "$path"
    ServerName $domain

    <Directory "$path">
        Options $options
        AllowOverride $override
        Order $order
        Deny from $deny
        Allow from $allow
        DirectoryIndex $index
    </Directory>$envVars
</VirtualHost>
EOT;
    }
}