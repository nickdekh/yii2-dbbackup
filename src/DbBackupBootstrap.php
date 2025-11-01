<?php
namespace nickdekh\dbbackup;

use yii\base\BootstrapInterface;
use yii\console\Application;

class DbBackupBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        // Only attach in console mode
        if ($app instanceof Application) {
            $app->controllerMap['db-backup'] = [
                'class' => 'nickdekh\\dbbackup\\Command\\DbBackupController',
            ];
        }
    }
}
