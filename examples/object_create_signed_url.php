<?php

require "../vendor/autoload.php";

use Bihuohui\HuaweicloudObs\ObsClient;

$config = [
    'key' => getenv('HUAWEI_CLOUD_OBS_ACCESS_KEY'),
    'secret' => getenv('HUAWEI_CLOUD_OBS_SECRET_KEY'),
    'endpoint' => getenv('HUAWEI_CLOUD_OBS_ENDPOINT'),
];

$collection = ObsClient::factory($config)->createSignedUrl([
    'Method' => 'GET',
    'Bucket' => 'jyg-test',
    'Key' => 'avatars/default.png',
    'Expires' => 3600, // URL有效期，3600秒
]);

var_dump($collection->toArray());