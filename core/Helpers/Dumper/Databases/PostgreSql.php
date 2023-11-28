<?php

namespace Core\Helpers\Dumper\Databases;

use Core\Helpers\Dumper\DbDumper;
use Core\Helpers\Dumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class PostgreSql extends DbDumper
{
    protected $useInserts = false;

    protected $createTables = true;

    /** @var false|resource */
    private $tempFileHandle;

    public function __construct()
    {
        $this->port = 5432;
    }

    public function useInserts()
    {
        $this->useInserts = true;

        return $this;
    }

    public function dumpToFile($dumpFile)
    {
        $this->guardAgainstIncompleteCredentials();

        $tempFileHandle = tmpfile();
        $this->setTempFileHandle($tempFileHandle);

        $process = $this->getProcess($dumpFile);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    public function getDumpCommand($dumpFile)
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}pg_dump{$quote}",
            "-U {$this->userName}",
            '-h ' . ($this->socket === '' ? $this->host : $this->socket),
            "-p {$this->port}",
        ];

        if ($this->useInserts) {
            $command[] = '--inserts';
        }

        if (!$this->createTables) {
            $command[] = '--data-only';
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        if (!empty($this->includeTables)) {
            $command[] = '-t ' . implode(' -t ', $this->includeTables);
        }

        if (!empty($this->excludeTables)) {
            $command[] = '-T ' . implode(' -T ', $this->excludeTables);
        }

        return $this->echoToFile(implode(' ', $command), $dumpFile);
    }

    public function getContentsOfCredentialsFile()
    {
        $contents = [
            $this->escapeCredentialEntry($this->host),
            $this->escapeCredentialEntry($this->port),
            $this->escapeCredentialEntry($this->dbName),
            $this->escapeCredentialEntry($this->userName),
            $this->escapeCredentialEntry($this->password),
        ];

        return implode(':', $contents);
    }

    protected function escapeCredentialEntry($entry)
    {
        $entry = str_replace('\\', '\\\\', $entry);
        $entry = str_replace(':', '\\:', $entry);

        return $entry;
    }

    public function guardAgainstIncompleteCredentials()
    {
        foreach (['userName', 'dbName', 'host'] as $requiredProperty) {
            if (empty($this->$requiredProperty)) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }

    protected function getEnvironmentVariablesForDumpCommand($temporaryCredentialsFile)
    {
        return [
            'PGPASSFILE' => $temporaryCredentialsFile,
            'PGDATABASE' => $this->dbName,
        ];
    }

    public function doNotCreateTables()
    {
        $this->createTables = false;

        return $this;
    }

    public function getProcess($dumpFile)
    {
        $command = $this->getDumpCommand($dumpFile);

        fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

        $envVars = $this->getEnvironmentVariablesForDumpCommand($temporaryCredentialsFile);

        return Process::fromShellCommandline($command, null, $envVars, null, $this->timeout);
    }

    /**
     * @return false|resource
     */
    public function getTempFileHandle()
    {
        return $this->tempFileHandle;
    }

    public function setTempFileHandle($tempFileHandle)
    {
        $this->tempFileHandle = $tempFileHandle;
    }
}
