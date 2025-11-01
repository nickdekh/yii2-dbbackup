<?php

namespace nickdekh\dbbackup\commands;

use Yii;
use yii\console\Controller;

class DbBackupController extends Controller
{
    function getDbInfo(string $component = 'db'): array
    {
        $db = Yii::$app->get($component);
        $info = [
            'username' => $db->username ?? null,
            'password' => $db->password ?? null,
            'driver' => null,
            'host' => null,
            'port' => null,
            'dbname' => null,
            'socket' => null,
            'dsn' => $db->dsn ?? null,
        ];

        if (empty($db->dsn)) {
            return $info;
        }

        // separate driver from parameters: "mysql:host=...;port=...;dbname=..."
        if (preg_match('/^([^:]+):(.*)$/', $db->dsn, $m)) {
            $info['driver'] = $m[1];
            $params = $m[2];

            // split by semicolon
            foreach (explode(';', $params) as $pair) {
                if ($pair === '') continue;
                // some DSNs have "=" between key and value
                if (strpos($pair, '=') !== false) {
                    list($k, $v) = explode('=', $pair, 2);
                    $k = trim($k);
                    $v = trim($v);
                    // normalize keys
                    if (in_array($k, ['host', 'hostname'])) $info['host'] = $v;
                    if ($k === 'port') $info['port'] = $v;
                    if (in_array($k, ['dbname', 'database'])) $info['dbname'] = $v;
                    if (in_array($k, ['unix_socket', 'socket'])) $info['socket'] = $v;
                } else {
                    // for cases like sqlite: "sqlite:/path/to/file.db"
                    // or parameters without "=" — ignore
                }
            }
        }

        return $info;
    }

    public function actionRun($dir = '/backup/db_backup', $max_age = 7 * 24 * 3600)
    {
        extract($this->getDbInfo());

        $parts = ["mysqldump --single-transaction=TRUE --user=$username --password=$password --host=$host"];
        if (!empty($port)) {
            $parts[] = "--port=$port";
        }
        $filename = $dir . DIRECTORY_SEPARATOR . $dbname . '-' . date('YmdHi') . '.sql.gz' ;
        $parts[] = "--quick --max_allowed_packet=512M $dbname | gzip -c > ${filename}";
        $command = implode(' ', $parts);

        `$command`;

        $olderThan = time() - $max_age;
        $deletedCount = 0;

        foreach (glob(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.sql.gz') as $file) {
            if (is_file($file) && filemtime($file) < $olderThan) {
                if (@unlink($file)) {
                    $deletedCount++;
                }
            }
        }
    }
}