<?php

namespace Core\Helpers\Dumper;

use Core\Helpers\Dumper\Compressors\Compressor;
use Core\Helpers\Dumper\Exceptions\CannotSetParameter;
use Core\Helpers\Dumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    protected $dbName = '';

    protected $userName = '';

    protected $password = '';

    protected $host = 'localhost';

    protected $port = 5432;

    protected $socket = '';

    protected $timeout = 0;

    protected $dumpBinaryPath = '';

    protected $includeTables = [];

    protected $excludeTables = [];

    protected $extraOptions = [];

    protected $extraOptionsAfterDbName = [];

    protected $compressor = null;

    public static function create()
    {
        return new static();
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function setDbName($dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    public function setSocket($socket)
    {
        $this->socket = $socket;

        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setDumpBinaryPath($dumpBinaryPath)
    {
        if ($dumpBinaryPath !== '' && !str_ends_with($dumpBinaryPath, '/')) {
            $dumpBinaryPath .= '/';
        }

        $this->dumpBinaryPath = $dumpBinaryPath;

        return $this;
    }

    public function getCompressorExtension()
    {
        return $this->compressor->useExtension();
    }

    public function useCompressor(Compressor $compressor)
    {
        $this->compressor = $compressor;

        return $this;
    }

    public function includeTables($includeTables)
    {
        if (!empty($this->excludeTables)) {
            throw CannotSetParameter::conflictingParameters('includeTables', 'excludeTables');
        }

        if (!is_array($includeTables)) {
            $includeTables = explode(', ', $includeTables);
        }

        $this->includeTables = $includeTables;

        return $this;
    }

    public function excludeTables($excludeTables)
    {
        if (!empty($this->includeTables)) {
            throw CannotSetParameter::conflictingParameters('excludeTables', 'includeTables');
        }

        if (!is_array($excludeTables)) {
            $excludeTables = explode(', ', $excludeTables);
        }

        $this->excludeTables = $excludeTables;

        return $this;
    }

    public function addExtraOption($extraOption)
    {
        if (!empty($extraOption)) {
            $this->extraOptions[] = $extraOption;
        }

        return $this;
    }

    public function addExtraOptionAfterDbName($extraOptionAfterDbName)
    {
        if (!empty($extraOptionAfterDbName)) {
            $this->extraOptionsAfterDbName[] = $extraOptionAfterDbName;
        }

        return $this;
    }

    abstract public function dumpToFile($dumpFile);

    public function checkIfDumpWasSuccessFul(Process $process, $outputFile)
    {
        if (!$process->isSuccessful()) {
            throw DumpFailed::processDidNotEndSuccessfully($process);
        }

        if (!file_exists($outputFile)) {
            throw DumpFailed::dumpfileWasNotCreated($process);
        }

        if (filesize($outputFile) === 0) {
            throw DumpFailed::dumpfileWasEmpty($process);
        }
    }

    protected function getCompressCommand($command, $dumpFile)
    {
        $compressCommand = $this->compressor->useCommand();

        if ($this->isWindows()) {
            return "{$command} | {$compressCommand} > {$dumpFile}";
        }

        return "(((({$command}; echo \$? >&3) | {$compressCommand} > {$dumpFile}) 3>&1) | (read x; exit \$x))";
    }

    protected function echoToFile($command, $dumpFile)
    {
        $dumpFile = '"' . addcslashes($dumpFile, '\\"') . '"';

        if ($this->compressor) {
            return $this->getCompressCommand($command, $dumpFile);
        }

        return $command . ' > ' . $dumpFile;
    }

    protected function determineQuote()
    {
        return $this->isWindows() ? '"' : "'";
    }

    protected function isWindows()
    {
        return str_starts_with(strtoupper(PHP_OS), 'WIN');
    }
}
