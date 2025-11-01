<?php

return  [
    'id' => 'dbbackup-test',
    'basePath' => __DIR__,
    'controllerNamespace' => 'nickdekh\dbbackup\commands',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=test',
            'username' => 'root',
            'password' => '',
        ],
    ],
    'controllerMap' => [
        'db-backup' => [
            'class' => 'nickdekh\dbbackup\commands\DbBackupController',
        ],
    ],
];