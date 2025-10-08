<?php

namespace App\Controller;

use App\Entity\Job;
use App\Service\FileService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    public function __construct(
        private readonly FileService $fileService
    ) {
    }

    #[Route(path: '/convert', methods: ['POST'], stateless: true)]
    public function convertAction(
        #[MapUploadedFile(
            new File([
                'mimeTypes' => [
                    'text/csv',
                    'text/plain', // not perfect for CSV
                    'application/json',
                    'application/vnd.oasis.opendocument.spreadsheet',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ],
                'mimeTypesMessage' => 'The only allowed formats are CSV, JSON, XLSX and ODS'
            ])
        )]
        UploadedFile $file,
        #[MapQueryParameter] string $format
    ): JsonResponse {
        if (!in_array($format, FileService::SUPPORTED_CONVERSIONS)) {
            return $this->json(['error' => 'Unsupported conversion output'], Response::HTTP_BAD_REQUEST);
        }

        try {
            return $this->json($this->fileService->register($file, $format));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route(path: '/job/{id}', methods: ['GET'], stateless: true)]
    public function getAction(
        #[MapEntity] Job $job,
    ): JsonResponse {
        return $this->json($job);
    }

    #[Route(path: '/job/{id}/download', methods: ['GET'], stateless: true)]
    public function downloadAction(
        #[MapEntity] Job $job,
    ): Response {
        if ($job->getStatus() !== FileService::STATUS_FINISHED) {
            return $this->json(['error' => 'The file is being converted or failed to be converted'], Response::HTTP_BAD_REQUEST);
        }

        return $this->file($job->getFilePath());
    }
}
