<?php

namespace App\Jobs;

use Core\Helpers\Dumper\Compressors\GzipCompressor;
use Core\Helpers\Dumper\Databases\MySql;
use Core\Helpers\Dumper\Databases\PostgreSql;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DumpDB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // filename
    protected $name;

    // keep max file backup
    protected $maxFile;

    // directory backup
    protected $path;

    public function __construct($args = [])
    {
        $this->name = getConfig('logs.dump_db.file_name');
        $this->maxFile = getConfig('logs.dump_db.max_file');
        $this->path = getConfig('logs.dump_db.path');
    }

    public function handle()
    {
        try {
            switch (env('DB_CONNECTION')) {
                case 'mysql':
                    MySql::create()
                        ->setHost(env('DB_HOST'))
                        ->setPort(env('DB_PORT'))
                        ->setDbName(env('DB_DATABASE'))
                        ->setUserName(env('DB_USERNAME'))
                        ->setPassword(env('DB_PASSWORD'))
                        // ->includeTables('users, admin_users')
                        // ->excludeTables('logs, logs_users')
                        ->useCompressor(new GzipCompressor())
                        ->dumpToFile($this->path . '/' . $this->name);
                    break;
                case 'pgsql':
                    PostgreSql::create()
                        ->setHost(env('DB_HOST'))
                        ->setPort(env('DB_PORT'))
                        ->setDbName(env('DB_DATABASE'))
                        ->setUserName(env('DB_USERNAME'))
                        ->setPassword(env('DB_PASSWORD'))
                        // ->includeTables('users, admin_users')
                        // ->excludeTables('logs, logs_users')
                        ->useCompressor(new GzipCompressor())
                        ->dumpToFile($this->path . '/' . $this->name);
                    break;
            }

            $this->_deleteFiles();
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }

    protected function _deleteFiles()
    {
        $dir = database_path('backup');
        $files = array_diff(scandir($dir), ['.', '..', '.gitignore']);
        $files = array_values($files);
        if (count($files) >= $this->maxFile) {
            $lists = array_slice($files, 0, count($files) - $this->maxFile);
            if (!empty($lists)) {
                foreach ($lists as $list) {
                    unlink($dir . '/' . $list);
                }
            }
        }
    }
}
