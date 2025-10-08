<?php

namespace App\Service;

use App\Entity\Job;
use App\Message\ConvertMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class FileService
{
    public const string STATUS_PENDING = 'PENDING';
    public const string STATUS_FINISHED = 'FINISHED';
    public const string STATUS_ERROR = 'ERROR';

    public const array SUPPORTED_CONVERSIONS = ['xml', 'json'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function register(UploadedFile $file, string $outputFormat): array
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $job = $this->entityManager->getRepository(Job::class)->findOneBy([
            'fileHash' => $hash,
            'status' => self::STATUS_PENDING
        ]);

        if ($job && $job->getOutputFormat() === $outputFormat) {
            throw new \Exception('The provided file has already been registered for this format.', Response::HTTP_CONFLICT);
        }

        $ext = $file->guessExtension();
        $fileName = sprintf('%s.%s', $hash, $ext);

        try {
            $file->move($this->parameterBag->get('app.storage_path'), $fileName);
        } catch (\Exception $e) {
            throw new \Exception('Failed to save the file', Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }

        $job = new Job()
            ->setFileHash($hash)
            ->setStatus(self::STATUS_PENDING)
            ->setOutputFormat($outputFormat)
            ->setFilePath(sprintf('%s%s', $this->parameterBag->get('app.storage_path'), $fileName))
            ->setInputExtension($ext)
        ;

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        try {
            $this->messageBus->dispatch(new ConvertMessage($job->getId()));
        } catch (ExceptionInterface $e) {
            throw new \Exception('Failed to put message in queue', Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }

        return [
            'job_id' => $job->getId(),
            'file_hash' => $hash,
            'output_format' => $outputFormat,
            'status' => self::STATUS_PENDING,
        ];
    }
}
