<?php

namespace App\Service\Converter;

use App\Entity\Job;
use Symfony\Component\Filesystem\Filesystem;

final class JsonConverter implements ConverterInterface
{
    public function toArray(Job $job): array
    {
        $content = new Filesystem()->readFile($job->getFilePath());

        return json_decode($content, true);
    }
}
