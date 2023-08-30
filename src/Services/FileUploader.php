<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\File;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use function uniqid;

final class FileUploader
{
    private readonly string $targetDirectory;
    private readonly SluggerInterface $slugger;
    private ?Filesystem $filesystem = null;

    public function __construct(
        #[Autowire(value: '%kernel.project_dir%/public/uploads')] string $targetDirectory,
        SluggerInterface $slugger,
    ) {
        $this->slugger = $slugger;
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $uploadedFile): File
    {
        $fileName = $this->generateFileName($uploadedFile);

        $file = new File($fileName, $uploadedFile->getMimeType(), $uploadedFile->getSize());

        $uploadedFile->move($this->targetDirectory, $fileName);

        return $file;
    }

    public function copy(string $filePath): File
    {
        $copyFile = new SymfonyFile($filePath);
        $fileName = $this->generateFileName($copyFile);
        $targetFile = $this->getPath($fileName);

        $file = new File($fileName, $copyFile->getMimeType(), $copyFile->getSize());

        $this->getFilesystem()->copy($copyFile->getRealPath(), $targetFile);

        return $file;
    }

    private function generateFileName(SymfonyFile $file): string
    {
        do {
            $originalPath = ($file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getBasename());
            $originalFilename = pathinfo($originalPath, PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
            $filePath = $this->getPath($fileName);
        } while ($this->getFilesystem()->exists($filePath));

        return $fileName;
    }

    public function getWebPath(File $file): string
    {
        return '/uploads/' . rawurlencode($file->getName());
    }

    public function delete(File $file): void
    {
        $fileName = $this->getFilePath($file);
        unlink($fileName);
    }

    public function getFilePath(File $file): string
    {
        return $this->getPath($file->getName());
    }

    private function getPath(string $fileName): string
    {
        return $this->targetDirectory . '/' . $fileName;
    }

    private function getFilesystem(): Filesystem
    {
        if ($this->filesystem === null) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }
}
