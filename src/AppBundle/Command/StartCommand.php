<?php
// src/AppBundle/Command/CreateUserCommand.php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('start')

            // description shown while running "php bin/console list"
            ->setDescription('Uses previously recorded states of node clusters and stacks to restore them')

            // the full command description shown when running the command with the "--help" option
            ->setHelp('Uses previously recorded nodes for each node cluster and states for each stack to scale node clusters back up and restore each stacks state');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting...');
    }
}