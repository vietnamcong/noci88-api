<?php

namespace Core\Helpers\Dumper\Databases;

use Core\Helpers\Dumper\DbDumper;
use Core\Helpers\Dumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MongoDb extends DbDumper
{
    protected $port = 27017;

    protected $collection = null;

    protected $authenticationDatabase = null;

    public function dumpToFile($dumpFile)
    {
        $this->guardAgainstIncompleteCredentials();

        $process = $this->getProcess($dumpFile);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    /**
     * Verifies if the dbname and host options are set.
     *
     * @throws CannotStartDump
     */
    public function guardAgainstIncompleteCredentials()
    {
        foreach (['dbName', 'host'] as $requiredProperty) {
            if (strlen($this->$requiredProperty) === 0) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    public function setAuthenticationDatabase($authenticationDatabase)
    {
        $this->authenticationDatabase = $authenticationDatabase;

        return $this;
    }

    public function getDumpCommand($filename)
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}mongodump{$quote}",
            "--db {$this->dbName}",
            '--archive',
        ];

        if ($this->userName) {
            $command[] = "--username '{$this->userName}'";
        }

        if ($this->password) {
            $command[] = "--password '{$this->password}'";
        }

        if (isset($this->host)) {
            $command[] = "--host {$this->host}";
        }

        if (isset($this->port)) {
            $command[] = "--port {$this->port}";
        }

        if (isset($this->collection)) {
            $command[] = "--collection {$this->collection}";
        }

        if ($this->authenticationDatabase) {
            $command[] = "--authenticationDatabase {$this->authenticationDatabase}";
        }

        return $this->echoToFile(implode(' ', $command), $filename);
    }

    public function getProcess($dumpFile)
    {
        $command = $this->getDumpCommand($dumpFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }
}
