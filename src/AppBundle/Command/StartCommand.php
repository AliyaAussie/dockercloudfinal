<?php
// src/AppBundle/Command/CreateUserCommand.php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


use DockerCloud;

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

        DockerCloud\Client::configure('aliyatulyakova','cacd0470-37b7-4d13-808c-5791b472dda5');

        $output->writeln('*********************Clusters********************');
       $this->scaleAllClusters($output);
         $output->writeln('*********************Stacks********************');
        $this->startAllStacks($output);
        $output->writeln('*********************Stack UUIDs********************');
          $this->getStackUUIDs($output);
    }



    public function scaleAllClusters(OutputInterface $output){

        $API = new DockerCloud\API\NodeCluster();
        $API->setOrganisationNamespace('ampco');
        $Response = $API->getList();
        $nClusters = $Response->getObjects();

        foreach ($nClusters as $nCluster){
            $output->writeln(("{$nCluster->getName()} {{$nCluster->getUuid()}} ({$nCluster->getState()})"));
        }
    }

    public function startStack(OutputInterface $output){

    }

    public function getStackUUIDs(OutputInterface $output){
        $API = new DockerCloud\API\Stack();
        $API->setOrganisationNamespace('ampco');
        $Response = $API->getList();
        $nUUIDs = $Response->getObjects();

        foreach ($nUUIDs as $nUUID) {

        }
    }


    public function startAllStacks(OutputInterface $output){
        $API = new DockerCloud\API\Stack();
        $API->setOrganisationNamespace('ampco');
        $Response = $API->getList();
        $nStacks = $Response->getObjects();

        foreach ($nStacks as $nStack) {
            $output->writeln("{$nStack->getName()} ({$nStack->getUuid()}) ({$nStack->getState()})");

        }

    }






    
    
    
    
}