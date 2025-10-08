<?php

namespace App\Service\Converter;

use App\Entity\Job;
use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsxConverter implements ConverterInterface
{
    public function toArray(Job $job): array
    {
        $results = [];
        $spreadsheet = IOFactory::load($job->getFilePath());

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $results[$worksheet->getTitle()] = $worksheet->toArray(formatData: false);
        }

        return $results;
    }
}
