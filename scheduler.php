<?php require_once  __DIR__.'/vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: aliyatulyakova
 * Date: 03/11/2017
 * Time: 16:25
 */

use GO\Scheduler;

$scheduler = new Scheduler();


$scheduler->raw('php bin/console stop')->at('* * * * *');
/*
 * should be at ('45 17 * * 1-5')
 */
$scheduler->run();
sleep(180);

/*
 * should be at ('00 09 * * 1-5')
 */
$scheduler->raw('php bin/console start')->at('* * * * *');
$scheduler->run();