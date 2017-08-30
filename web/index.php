<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Sgmendez\Json\Json;

$json = new Json();

die(';D');

// read json
try
{
    $dataArray = $json->decodeFile('./posts.json');
    echo '<pre>';
    print_r($dataArray);
}
catch (Exception $ex)
{
    echo '[EXCEPTION] MSG: '.$ex->getMessage() .
         ' | FILE: '.$ex->getFile().': '.$ex->getLine()."\n";
}

#################################### CONFIG ####################################
$config = Yaml::parse(file_get_contents('./config.yml'));
$debug = true;
$truncatedDebug = false;
################################################################################

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

// authentication
try {
    $ig->setUser(
        $config['instagram']['credentials']['username'],
        $config['instagram']['credentials']['password']
    );

    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

// // read json and create the post
foreach ($dataArray as $key => $post) {
    $timePost = new DateTime($post['time'], new DateTimeZone('Australia/Sydney'));
    $now = new DateTime('NOW', new DateTimeZone('Australia/Sydney'));
    echo '<pre>';

    if ($now->format('d/m/Y h:i:s') >= $timePost->format('d/m/Y h:i:s')) {
        echo $timePost->format('d/m/Y h:i:s');
        unset($dataArray[$key]);
    }

    // print_r($d->format('Y-m-d H:i:s'));


    try {
        $ch = curl_init($post['file']);
        $fp = fopen('./image.jpg', 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $ig->timeline->uploadPhoto('./image.jpg', ['caption' => $post['caption']]);
        unlink('./image.jpg');
    } catch (\Exception $e) {
        echo 'Something went wrong: '.$e->getMessage()."\n";
    }
}

// new posts.json
// print_r($dataArray);
