<?php
// src/AppBundle/Command/CreateUserCommand.php
namespace AppBundle\Command;

use DockerCloud\API\Stack as ST_API;
use DockerCloud\API\NodeCluster as NC_API;
use DockerCloud\Model\Response\StackGetListResponse;
use DockerCloud\Model\Stack as Model;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\Tests\Fixtures\Input;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

//Stacks commands
define('CMD_START_STACK', 'docker-cloud stack start');
define('CMD_STACK_INSPECT', 'docker-cloud stack inspect');

//NodeClusters commands
define('CMD_NC_SCALE', 'docker-cloud nodecluster scale');

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

        $output->writeln('******************Get Node Cluster by UUID********************');
        $this->getNodeCluster($output);
        $output->writeln('******************Scale Node Cluster********************');
        $this->scaleNodeCluster($output);
        $output->writeln('******************Scale All Clusters********************');
        $this->scaleAllClusters($output);

         $output->writeln('*********************Stacks********************');
        $this->startAllStacks($output);
        $output->writeln('*********************Get Stack by UUID********************');
   $this->getStack($output);
        $output->writeln('*********************Start Stack********************');
        $this->startStack($output);

    }

    //Get NodeCluster by UUID (test)
    public function getNodeCluster(OutputInterface $output){
        $NC_API = new NC_API();
        $NC_API->setOrganisationNamespace('ampco');
        $NCResponse = $NC_API->get('088a5ede-0680-4394-804d-0aa8eecb0eb7');
        $output->writeln("{$NCResponse->getName()} {$NCResponse->getState()} {$NCResponse->getCurrentNumNodes()}");
    }

    //Scale Single NodeCluster (test)
    public function scaleNodeCluster(OutputInterface $output){
        $NC_API = new NC_API();
        $NC_API->setOrganisationNamespace('ampco');
        $NCResponse = $NC_API->get('088a5ede-0680-4394-804d-0aa8eecb0eb7');
        $output = shell_exec(CMD_NC_SCALE.' '.$NCResponse->getUuid().' 1');
        echo $output;
    }

    //Scale All Clusters
    public function scaleAllClusters(OutputInterface $output){
        $NC_API = new NC_API();
        $NC_API->setOrganisationNamespace('ampco');
        $NCResponse = $NC_API->getList();
        $nclusters = $NCResponse->getObjects();

        foreach ($nclusters as $ncluster){
            $numb_nodes = $ncluster->getCurrentNumNodes();
            if($numb_nodes != 0 && $numb_nodes != 2){
//                $output = shell_exec(CMD_NC_SCALE.' '.$ncluster->getUuid().' '.$numb_nodes);
                echo $ncluster->getName().' '.$ncluster->getState().' '.$ncluster->getCurrentNumNodes()."\r\n";
            } elseif ($numb_nodes = 2 && $numb_nodes !=0) {
                //            $output = shell_exec(CMD_NC_SCALE.' '.$ncluster->getUuid().' '.$numb_nodes);
              echo $ncluster->getName().' '.$ncluster->getState().' '.$ncluster->getCurrentNumNodes()."\r\n";
            }
        }
    }

    //Get Stack by UUID (test)
    public function getStack(OutputInterface $output){
        $StackAPI = new ST_API();
        $StackAPI->setOrganisationNamespace('ampco');
        $StackResponse = $StackAPI->get('78982ec0-6c27-4799-9fe7-15ee367f2e72');
        $output->writeln("{$StackResponse->getName()} ({$StackResponse->getState()})");     }


    //Start Single Stack (test)
    public function startStack(OutputInterface $output){
        $StackAPI = new ST_API;
        $StackAPI->setOrganisationNamespace('ampco');
        $StackResponse = $StackAPI->get('78982ec0-6c27-4799-9fe7-15ee367f2e72');
        $output = shell_exec(CMD_START_STACK.' '.$StackResponse->getUuid());
           echo $output;
    }

    //Start All Stacks
    public function startAllStacks(OutputInterface $output){
        $StackAPI = new ST_API();
        $StackAPI->setOrganisationNamespace('ampco');
        $StackGetResponse = $StackAPI->getList();
        $stacks = $StackGetResponse->getObjects();

        foreach ($stacks as $stack) {

                $output = shell_exec(CMD_START_STACK . ' ' . $stack->getUuid());
                echo $stack->getName() . " " . $stack->getState() . "\r\n";
            }

    }








    
}