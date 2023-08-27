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

final readonly class FileUploader
{
    private string $targetDirectory;
    private SluggerInterface $slugger;

    public function __construct(
        #[Autowire(value: '%kernel.project_dir%/public/uploads')] string $targetDirectory,
        SluggerInterface $slugger,
    ) {
        $this->slugger = $slugger;
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $uploadedFile): File
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

        $file = new File($fileName, $uploadedFile->getMimeType(), $uploadedFile->getSize());

        $uploadedFile->move($this->targetDirectory, $fileName);

        return $file;
    }

    public function copy(string $filePath): File
    {
        $fileSystem = new Filesystem();

        $file = new SymfonyFile($filePath);
        do {
            $targetPath = sys_get_temp_dir() . '/' . uniqid('avatar') . '.' . $file->getExtension();
        } while ($fileSystem->exists($targetPath));
        $fileSystem->copy($file->getRealPath(), $targetPath, true);

        $file = new SymfonyFile($targetPath);
        return $this->upload(new UploadedFile($file->getRealPath(), $file->getBasename(), $file->getMimeType(), test: true));
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
        return $this->targetDirectory . '/' . $file->getName();
    }
}
