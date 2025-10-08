<?php

namespace App\Service\Converter;

use App\Entity\Job;
use Symfony\Component\Filesystem\Filesystem;

final class CsvConverter implements ConverterInterface
{
    public function toArray(Job $job): array
    {
        $content = new Filesystem()->readFile($job->getFilePath());

        return str_getcsv($content);
    }
}
