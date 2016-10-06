<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../vendor/yiisoft/yii2/Yii.php';

$app = new \yii\console\Application([
    'id' => 'application',
    'basePath' => __DIR__,
    'components' => [
        'urlManager' => [
            'hostInfo' => 'http://localhost',
        ],
    ],
]);
