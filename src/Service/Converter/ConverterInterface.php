<?php

namespace App\Service\Converter;

use App\Entity\Job;

interface ConverterInterface
{
    public function toArray(Job $job): array;
}
