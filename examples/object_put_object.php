<?php

require "../vendor/autoload.php";

use Bihuohui\HuaweicloudObs\ObsClient;

$config = [
    'key' => getenv('HUAWEI_CLOUD_OBS_ACCESS_KEY'),
    'secret' => getenv('HUAWEI_CLOUD_OBS_SECRET_KEY'),
    'endpoint' => getenv('HUAWEI_CLOUD_OBS_ENDPOINT'),
];

$collection = ObsClient::factory($config)->putObject([
    'Bucket' => 'jyg-test',
    'Key' => 'avatars/default.png',
    'Body' => fopen('avatar.png', 'r'),
]);

var_dump($collection->toArray());