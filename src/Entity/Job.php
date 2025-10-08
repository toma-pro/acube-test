<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
class Job
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Could have been based on an Enum
     */
    #[ORM\Column(length: 20)]
    private string $status;

    #[ORM\Column(length: 255)]
    private string $fileHash;

    #[ORM\Column(length: 10)]
    private string $outputFormat;

    #[ORM\Column(length: 10)]
    private string $inputExtension;

    #[ORM\Column(length: 255)]
    #[Ignore]
    private string $filePath;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFileHash(): string
    {
        return $this->fileHash;
    }

    public function setFileHash(string $fileHash): self
    {
        $this->fileHash = $fileHash;

        return $this;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function setOutputFormat(string $outputFormat): self
    {
        $this->outputFormat = $outputFormat;

        return $this;
    }

    public function getInputExtension(): string
    {
        return $this->inputExtension;
    }

    public function setInputExtension(string $inputExtension): self
    {
        $this->inputExtension = $inputExtension;

        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }
}
