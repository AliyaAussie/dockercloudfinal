<?php
// src/AppBundle/Command/CreateUserCommand.php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DockerCloud;


class StopCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('stop')

            // description shown while running "php bin/console list"
            ->setDescription('Records the state of all running node clusters and stacks and stops them.')

            // the full command description shown when running the command with the "--help" option
            ->setHelp('Records the state of all running node clusters and stacks, then sets the number of nodes for each node cluster to 0, which automatically stops the related stack');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Stopping...');

        DockerCloud\Client::configure('aliyatulyakova','cacd0470-37b7-4d13-808c-5791b472dda5');

        $API = new DockerCloud\API\NodeCluster();
        $Response = $API->getList();
        $nClusters = $Response->getObjects();

        foreach ($nClusters as $nCluster) {
            $output->writeln("{$nCluster->getName()} ({$nCluster->getUuid()})");
        }
        
    }
}