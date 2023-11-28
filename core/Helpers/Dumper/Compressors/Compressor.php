<?php

namespace Core\Helpers\Dumper\Compressors;

interface Compressor
{
    public function useCommand(): string;

    public function useExtension(): string;
}
