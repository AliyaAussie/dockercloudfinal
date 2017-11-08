<?php
// src/AppBundle/Command/CreateUserCommand.php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('help')

            // description shown while running "php bin/console list"
            ->setDescription('Provides help for available commands and options');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('php bin/console stop ---->'.' Records the state of all running nodes and stacks; Scales Node Clusters to 0 and stops running stacks ');
        $output->writeln('php bin/console start ---->'.' Restores previously recorded states of nodes and stacks');
        $output->writeln('php bin/console auth ----->' . ' Authenticates users');
    }
}