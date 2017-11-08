<?php
/**
 * Created by PhpStorm.
 * User: aliyatulyakova
 * Date: 07/11/2017
 * Time: 10:02
 */
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class AuthCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('auth')
            ->setDescription('Log in.')
            ->setHelp('This command allows you to log in');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '======================================',
            'Log in',
            '======================================',
            '',
        ]);

       $this->userAuth($input, $output);

    }

    public function userAuth(InputInterface $input, OutputInterface $output){

        $file = 'var/data/AuthStory.txt';

        echo "Enter username: ";
        $username = fopen("php://stdin","w+");
        $username = fgets($username);

         echo "Enter password: ";
        $password = fopen("php://stdin", "w+");
        $password = fgets($password);


        date_default_timezone_set('Europe/London');

        $array = explode("\r\n", file_get_contents('var/data/Users.txt'));
        $size = count($array);



        for ($i=0; $i<$size; $i++){
            if(trim($username) == $array[$i]){
                if(trim($password) == $array[$i+1]) {
                    $output->writeln('Logged in!');
                    $data = 'Logged in'."\r\n".date('d/m/Y h:i:s a', time());
                    file_put_contents($file, $data);
                    break;
                }

                else {
                    $output->writeln('Invalid username or password');
                    $data = ' ';
                    file_put_contents($file, $data);
                    break;
                }
            }
            else {
                $output->writeln('Invalid username or password');
                $data = ' ';
                file_put_contents($file, $data);
                break;
            }


        }




    }

}