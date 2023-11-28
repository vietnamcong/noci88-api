<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Core\Helpers\Zipper\Zipper;

class ZipLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $args = [];

    protected $type;

    protected $keepDay;

    protected $item;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($args = [])
    {
        $this->type = data_get($args, 0, 'daily');
        $this->item = data_get($args, 1, null);
        $this->keepDay = getConfig('logs.zip_log.keep_day');
    }

    /**
     * Zip log file
     * type: month | daily
     * item:
     *     if type = month, item = 2022-01 | 2022-02, then zip file log by according to specific month specified. EX: 2022-01.zip, 2022-02.zip
     *     if type = daily, item = 2022-01-01 | 2022-01-02, then zip file log by according to specific day specified. EX: 2022-01-01.zip, 2022-01-02.zip
     * month: 2022-01-01, 2022-01-02...2022-01-31 -> 2022-01.zip
     * daily: 2022-01-01, 2022-01-02 -> 2022-01-01.zip, 2022-01-02.zip
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->type) {
            case 'month': // month
                $this->_month();
                break;
            default: // daily
                $this->_daily();
                break;
        }
    }

    protected function _month()
    {
        try {
            if (!empty($this->item)) {
                $start = Carbon::parse($this->item)->firstOfMonth()->toDateString();
                $end = Carbon::parse($this->item)->endOfMonth()->toDateString();
                $filename = $this->item . '.zip';
            } else {
                $start = Carbon::now()->startofMonth()->subMonth()->firstOfMonth()->toDateString();
                $end = Carbon::now()->startofMonth()->subMonth()->endOfMonth()->toDateString();
                $filename = Carbon::now()->startOfMonth()->subMonth()->format('Y-m') . '.zip';
            }

            $logsDir = storage_path("logs");

            $zipper = new Zipper();
            $zipper->make($logsDir . '/' . $filename);
            $listFolder = [];
            for ($i = strtotime($start); $i <= strtotime($end); $i = $i + (60 * 60 * 24)) {
                $date = date('Y-m-d', $i);
                if (file_exists($logsDir . '/' . $date)) {
                    $listFolder[] = $logsDir . '/' . $date;
                    $zipper->folder($date)->add($logsDir . '/' . $date);
                }
            }
            $zipper->close();
            $this->_deleteFolders($listFolder);
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }

    protected function _daily()
    {
        try {
            if (!empty($this->item)) {
                $date = $this->item;
                $filename = $this->item . '.zip';
            } else {
                $date = Carbon::now()->subDays($this->keepDay)->format('Y-m-d');
                $filename = $date . '.zip';
            }

            $logsDir = storage_path("logs");

            if (file_exists($logsDir . '/' . $date)) {
                $zipper = new Zipper();
                $zipper->make($logsDir . '/' . $filename)->folder($date)->add($logsDir . '/' . $date)->close();
                $this->deleteDir($logsDir . '/' . $date);
            }
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }

    /**
     * @param $folders
     */
    protected function _deleteFolders($folders)
    {
        if (empty($folders)) {
            return;
        }

        foreach ($folders as $folder) {
            if (file_exists($folder)) {
                $this->deleteDir($folder);
            }
        }
    }

    /**
     * @param $dir
     * @return bool
     */
    protected function deleteDir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir($dir . '/' . $file)) {
                $this->deleteDir($dir . '/' . $file);
            } else {
                unlink($dir . '/' . $file);
            }
        }
        return rmdir($dir);
    }
}
