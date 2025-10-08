<?php

namespace App\MessageHandler;

use App\Entity\Job;
use App\Message\ConvertMessage;
use App\Service\Converter\ConverterInterface;
use App\Service\Converter\CsvConverter;
use App\Service\Converter\JsonConverter;
use App\Service\Converter\XlsxConverter;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;

#[AsMessageHandler]
class FileConverterHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(ConvertMessage $message): void
    {
        $job = $this->entityManager->find(Job::class, $message->getId());

        if ($job) {
            try {
                $converter = $this->getConverter($job);
                $output = $this->convert($converter, $job);

                $fs = new Filesystem();
                $fs->remove($job->getFilePath());
                $finalName = sprintf('%s%s.%s',
                    $this->parameterBag->get('app.storage_path'),
                    basename($job->getFilePath(), sprintf('.%s', $job->getInputExtension())),
                    $job->getOutputFormat()
                );
                $fs->dumpFile($finalName, $output);

                $job->setFilePath($finalName);
                $job->setStatus(FileService::STATUS_FINISHED);
            } catch (\Exception $e) {
                $job->setStatus(FileService::STATUS_ERROR);
            }

            $this->entityManager->flush();
        }
    }

    private function convert(ConverterInterface $converter, Job $job): string
    {
        $array = $converter->toArray($job);

        return $this->serializer->serialize($array, $job->getOutputFormat());
    }

    private function getConverter(Job $job): ConverterInterface
    {
        $converter = match ($job->getInputExtension()) {
            'xlsx', 'ods' => new XlsxConverter(),
            'json' => new JsonConverter(),
            'csv', 'txt' => new CsvConverter(), // Handling txt without further check is not perfect
            default => null,
        };

        if (!$converter) {
            throw new \Exception(sprintf('No converter found for the job %s', $job->getId()));
        }

        return $converter;
    }
}
