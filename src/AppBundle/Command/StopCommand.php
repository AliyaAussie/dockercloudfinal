<?php
// src/AppBundle/Command/CreateUserCommand.php
namespace AppBundle\Command;

use DockerCloud\API\NodeCluster as NC_API;
use DockerCloud\API\Stack as ST_API;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DockerCloud;



//Node Cluster commands
define('CMD_SCALE_NC','docker-cloud nodecluster scale');
define('CMD_NC_INSPECT','docker-cloud nodecluster inspect');
define('CMD_NC_LIST', 'docker-cloud nodecluster ls');

//Stacks commands
define('CMD_INSPECT_STACK','docker-cloud stack inspect');
define('CMD_STACK_STOP','docker-cloud stack stop');

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

        $file = 'var/data/AuthStory.txt';

        $array = explode("\r\n", file_get_contents('var/data/AuthStory.txt'));
        $size = count($array);

        for($i=0; $i<$size; $i++) {

                if ($array[$i] == 'Logged in') {

                    $output->writeln('You need to be authorized at docker first.');

                    $output->write('Enter username: ');
                    $username = fopen("php://stdin","w+");
                    $username = fgets($username);

                    $output->write('Enter apiKey: ');
                    $apiKey = fopen("php://stdin", "w+");
                    $apiKey = fgets($apiKey);

                    DockerCloud\Client::configure(trim($username), trim($apiKey));

                    $output->writeln('Stopping...');

//                    DockerCloud\Client::configure('aliyatulyakova', 'cacd0470-37b7-4d13-808c-5791b472dda5');

                    $output->writeln("***************Scale All Node Clusters******************");
                    $this->scaleAllNodeClusters($output);
                    sleep(180);
                    $output->writeln("***************Stop All stacks******************");
                    $this->stopAllStacks($output);

                    //Clear AuthStory file
                    file_put_contents($file,' ');
                    break;
                } else {
                    $output->writeln('You must log in first');
                } break;
            }



    }

    // Scale Single NodeCluster to 0 (test)
    public function scaleNodeClusterTo0(OutputInterface $output){
       $NC_API = new NC_API();
        $NC_API->setOrganisationNamespace('ampco');
        $NC_Response = $NC_API->get('088a5ede-0680-4394-804d-0aa8eecb0eb7');
        if($NC_Response->getCurrentNumNodes() != 0){
            $numb_nodes = $NC_Response->getCurrentNumNodes();
            $file = 'var/data/NodeClustersState.txt';
            $handle = fopen($file, 'a');
            $data = $NC_Response->getUuid()."\r\n".$NC_Response->getCurrentNumNodes()."\r\n";
            fwrite($handle, $data);
            $output = shell_exec(CMD_SCALE_NC.' '.$NC_Response->getUuid().' 0');
            echo $NC_Response->getName().' : Scaling Node clusters to 0';
        } else {
            echo 'Number of nodes is already 0'."\r\n";
        }
    }

    //Scale NodeClusters
    public function scaleAllNodeClusters(OutputInterface $output){
        $NC_API = new NC_API();
        $NC_API->setOrganisationNamespace('ampco');
        $NC_Response = $NC_API->getList();
        $nclusters = $NC_Response->getObjects();
         foreach ($nclusters as $ncluster) {
             if ($ncluster->getCurrentNumNodes() == 0) {
//                 $output->writeln($ncluster->getName().' is already scaled to 0');
             }
             elseif ($ncluster->getCurrentNumNodes() != 0){
                 $numb_nodes = $ncluster->getCurrentNumNodes();
                 $file = 'var/data/NodeClustersState.txt';
                 $handle = fopen($file, 'a');
                 $data = $ncluster->getUuid()."\r\n".$ncluster->getCurrentNumNodes()."\r\n";
                 fwrite($handle, $data);

                 echo $ncluster->getName().' | Scaling to 0'."\r\n";
                 $output = shell_exec(CMD_NC_SCALE . ' ' . $ncluster->getUuid(). ' 0');
             }
         }
    }


//    Stop Single Stack (test)
    public function stopStack(OutputInterface $output){
        $ST_API = new ST_API();
        $ST_API->setOrganisationNamespace('ampco');
        $ST_Response = $ST_API->get('079d8534-5153-45fd-be83-94d2acb05878');
          if ($ST_Response->getState() == 'Running' || $ST_Response->getState() == 'Partly running'){
              $file = 'var/data/StacksState.txt';
              $handle = fopen($file, 'a');
              $data = $ST_Response->getUuid()."\r\n".$ST_Response->getState()."\r\n";
              fwrite($handle, $data);
              $output = shell_exec(CMD_STACK_STOP.' '.$ST_Response->getUuid());
              echo 'Stopping the stack '.$ST_Response->getName();
          } elseif ($ST_Response->getState() == 'Not running'){
              echo $ST_Response->getName().' is already stopped. Current state is '.$ST_Response->getState()."\r\n";
          }

    }

    //Stop All Stacks
    public function stopAllStacks(OutputInterface $output){
        $ST_API = new ST_API();
        $ST_API->setOrganisationNamespace('ampco');
        $ST_Response = $ST_API->getList();
        $stacks = $ST_Response->getObjects();
          foreach ($stacks as $stack){
              if($stack->getState() == 'Running' || $stack->getState() == 'Partly running'){
                  $file = 'var/data/StacksState.txt';
                  $handle = fopen($file, 'a');
                  $data = $stack->getUuid()."\r\n".$stack->getState()."\r\n";
                  fwrite($handle, $data);
                  echo $stack->getName().' | Stopping'."\r\n";
                  $output = shell_exec(CMD_STACK_STOP.' '.$stack->getUuid());
              }
              else {
//                  $output = shell_exec(CMD_STACK_STOP.' '.$stack->getUuid());
              }

          }
    }


}