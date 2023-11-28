<?php

namespace Core\Helpers;

class ExportCsv
{
    public $filename = '';

    // file extension
    const FILE_EXTENSION = '.csv';

    public function __construct($filename = '')
    {
        if (empty($filename)) {
            $filename = 'export_' . date('YmdHis');
        }
        $this->filename = $filename;
    }

    /**
     * @param $dataHeader
     * @param $dataExport
     * @param bool $isSJIS
     * @param string $delimiter
     * @param bool $notHeader
     */
    public function export($dataHeader, $dataExport, bool $isSJIS = true, string $delimiter = ',', bool $notHeader = false)
    {
        $filename = $this->filename . self::FILE_EXTENSION;
        $newHeader = [];

        if ($isSJIS) { // SJIS
            $filename = $this->_setFilenameSJIS($filename);
            if (!empty($dataHeader)) {
                $newHeader = $this->_setHeaderSJIS($dataHeader);
            }

            !empty($dataExport) && count($dataExport) >= 1 ? $dataExport = $this->_setExportDataSJIS($dataExport) : null;
            if (!$notHeader) {
                header('Content-type: application/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }
            $csvFile = fopen('php://output', 'w');
        } else { // UTF-8 BOM
            if (!$notHeader) {
                header('Content-Encoding: UTF-8');
                header('Content-type: application/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }

            $csvFile = fopen('php://output', 'w');
            // Insert the UTF-8 BOM in the file
            fputs($csvFile, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            $newHeader = $dataHeader;
        }

        if (!empty($newHeader)) {
            $this->_putItemCsv($csvFile, $dataHeader, $delimiter);
        }

        if (!empty($dataExport) && count($dataExport) >= 1) {
            foreach ($dataExport as $keyData => $valueData) {
                $this->_putItemCsv($csvFile, $valueData, $delimiter);
            }
        }

        fclose($csvFile);
        exit;
    }

    /**
     * @param $filename
     * @return bool|false|string|string[]|null
     */
    protected function _setFilenameSJIS($filename)
    {
        return mb_convert_encoding($filename, 'SJIS', 'UTF-8');
    }

    /**
     * @param $headers
     * @return array
     */
    protected function _setHeaderSJIS($headers): array
    {
        $data = [];
        foreach ($headers as $k => $header) {
            $data[$k] = mb_convert_encoding($header, 'SJIS', 'UTF-8');
        }
        return $data;
    }

    /**
     * @param $dataExport
     * @return array
     */
    protected static function _setExportDataSJIS($dataExport): array
    {
        $data = [];
        foreach ($dataExport as $k => $exports) {
            foreach ($exports as $field => $export) {
                $data[$k][$field] = mb_convert_encoding($export, 'SJIS-win', 'UTF-8');
            }
        }
        return $data;
    }

    /**
     * @param $handle
     * @param $item
     * @param $delimiter
     * @return false|int
     */
    protected function _putItemCsv($handle, $item, $delimiter)
    {
        $item = array_map(function ($value) {
            return '"' . $value . '"';
        }, $item);

        return fputs($handle, implode($delimiter, $item) . "\r\n");
    }
}
