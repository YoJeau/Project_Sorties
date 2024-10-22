<?php

namespace App\Service;

use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class CSVService
{
    /**
     * Gets records from CSV file
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     * @throws UnavailableStream
     */
    public function getRecords(string $filePath): array
    {
        $reader = Reader::createFromPath($filePath, 'r');

        $reader->setHeaderOffset(0);
        $iterator = $reader->getRecords();

        $records = [];
        foreach ($iterator as $offset => $record) {
            $records[] = $record;
        }

        return $records;
    }
}
