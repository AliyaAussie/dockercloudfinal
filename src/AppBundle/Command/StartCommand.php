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
        $file = 'var/data/AuthStory.txt';

        $array = explode("\r\n", file_get_contents('var/data/AuthStory.txt'));
        $size = count($array);

        for($i=0; $i<$size; $i++){
            if($array[$i] == 'Logged in'){

                $output->writeln('You need to be authorized at docker first.');

                $output->write('Enter username: ');
                $username = fopen("php://stdin","w+");
                $username = fgets($username);

                $output->write('Enter apiKey: ');
                $apiKey = fopen("php://stdin", "w+");
                $apiKey = fgets($apiKey);

                DockerCloud\Client::configure(trim($username), trim($apiKey));


                $output->writeln('Starting...');

//                DockerCloud\Client::configure('aliyatulyakova','cacd0470-37b7-4d13-808c-5791b472dda5');

                        $output->writeln('******************Scale All Clusters********************');
        $this->scaleAllClusters($output);
        sleep(180);
         $output->writeln('*********************Stacks********************');
        $this->startAllStacks($output);

                //Clear AuthStory file
                file_put_contents($file,' ');
                 break;


            } else {
                $output->writeln('You must be logged in first');

            }
        }
        

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

        $array = explode("\r\n", file_get_contents('var/data/NodeClustersState.txt'));
        $size = count($array);

         for ($i=0; $i<$size; $i++){
             if($NCResponse->getUuid() == $array[$i]){
                 if($NCResponse->getCurrentNumNodes() != $array[$i+1]){
                     echo 'Matches from last time '."\r\n";
                     $numb = $array[$i+1];
                     $output = shell_exec(CMD_NC_SCALE.' '.$NCResponse->getUuid().' '.$numb);
                     echo $output;
                 } elseif ($NCResponse->getCurrentNumNodes() == $array[$i+1]) {
                     echo $NCResponse->getName().' has already deployed'."\r\n";
                 }
             }
         }
         $this->deleteNodeClustersStateFile();
    }

    //Scale All Clusters
    public function scaleAllClusters(OutputInterface $output){
        $NC_API = new NC_API();
        $NC_API->setOrganisationNamespace('ampco');
        $NCResponse = $NC_API->getList();
        $nclusters = $NCResponse->getObjects();

        $array = explode("\r\n", file_get_contents('var/data/NodeClustersState.txt'));
        $size = count($array);

        foreach ($nclusters as $ncluster){
             for ($i=0; $i<$size; $i++){
                 if($ncluster->getUuid() == $array[$i]){
                     if($ncluster->getCurrentNumNodes() != $array[$i+1]) {
                         echo 'Was deployed last time';
                         $numb_nodes = $array[$i+1];
//                     $output = shell_exec(CMD_NC_SCALE.' '.$ncluster->getUuid().' '.$numb_nodes);
                         echo $ncluster->getName() . ' | Number of nodes: ' . $numb_nodes . "\r\n";
                     } elseif ($ncluster->getCurrentNumNodes() == $array[$i+1]) {
//                         $output = shell_exec(CMD_NC_SCALE.' '.$ncluster->getUuid().' '.'0');
                         echo 'Already deployed. ';
                         echo $ncluster->getName().' has '.$ncluster->getCurrentNumNodes().' node(s) '.$ncluster->getState()."\r\n";
                     } else {
                         echo $ncluster->getName().' has '.$ncluster->getCurrentNumNodes().' node(s). Scaling to '.$ncluster->getCurrentNumNodes()."\r\n";
//                         $output = shell_exec(CMD_NC_SCALE.' '.$ncluster->getUuid().' '.$ncluster->getCurrentNumNodes());
                     }
                 }
             }

        }
        $this->deleteNodeClustersStateFile();
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
        $StackResponse = $StackAPI->get('079d8534-5153-45fd-be83-94d2acb05878');

        $array = explode("\r\n", file_get_contents('var/data/StacksState.txt'));
        $size = count($array);

        for ($i = 0; $i < $size; $i++) {
            if($StackResponse->getUuid() == $array[$i]){
                if($StackResponse->getState() != $array[$i]){
                    echo 'Starting the stack: '.$StackResponse->getName();
                    $output = shell_exec(CMD_START_STACK . ' ' . $StackResponse->getUuid());
                    echo $output;
                } else {
                    echo 'Stack is already running';
                }
            }
        }
        $this->deleteStackFile();
//        $output = shell_exec(CMD_START_STACK.' '.$StackResponse->getUuid());
//           echo $output;
    }

    //Start All Stacks
    public function startAllStacks(OutputInterface $output)
    {
        $StackAPI = new ST_API();
        $StackAPI->setOrganisationNamespace('ampco');
        $StackGetResponse = $StackAPI->getList();
        $stacks = $StackGetResponse->getObjects();

        $array = explode("\r\n", file_get_contents('var/data/StacksState.txt'));
        $size = count($array);
        foreach ($stacks as $stack) {
            for ($i = 0; $i < $size; $i++) {
                if ($stack->getUuid() == $array[$i]) {
                    if ($stack->getState() != $array[$i + 1]) {
                        if ($stack->getState() == 'Not running' || $stack->getState() == 'Terminated'){
                            echo 'The stack is '.$stack->getState()."\r\n";
                        } else {
                        echo 'Was running last time. ';
                        $output = shell_exec(CMD_START_STACK . ' ' . $stack->getUuid());
                        echo $stack->getName() . ' | Starting..' . "\r\n";
                        }
                    } elseif ($stack->getState() == $array[$i+1]){
                        echo $stack->getName().' is already running '."\r\n";
                    }

                }
            }
        }

            $this->deleteStackFile();

    }

    public function readFile(){
        $stackFile = file('var/data/StacksState.txt');
        $array = explode("\r\n", file_get_contents('var/data/StacksState.txt'));
        $size = count($array);
        echo $size;
         for ($i=0; $i < $size; $i++){

             echo "This is the ".$array[$i].' numb'."\r\n";
         }

    }

    public function deleteNodeClustersStateFile(){
        $nodeClusterFile = 'var/data/NodeClustersState.txt';
        unlink($nodeClusterFile);
    }

    public function deleteStackFile(){
        $stackFile = 'var/data/StacksState.txt';
        unlink($stackFile);

    }
    
}