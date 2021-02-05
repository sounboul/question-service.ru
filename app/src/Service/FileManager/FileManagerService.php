<?php
namespace App\Service\FileManager;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Сервис для управления загрузкой файлов
 */
class FileManagerService implements FileManagerServiceInterface
{
    /**
     * @var string Путь до расположения файлов
     */
    private string $targetPath;

    /**
     * @var string Путь до папки расположения файлов
     */
    private string $folderPath;

    /**
     * @var Filesystem File System
     */
    private Filesystem $fileSystem;

    /**
     * Конструктор
     *
     * @param string $targetPath
     * @param string $folderPath
     * @param Filesystem $fileSystem
     */
    public function __construct(string $targetPath, string $folderPath, Filesystem $fileSystem)
    {
        $this->setTargetPath($targetPath);
        $this->setFolderPath($folderPath);

        $this->fileSystem = $fileSystem;
    }

    /**
     * @return string Путь до расположения файлов
     */
    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    /**
     * Установить путь до расположения файлов
     *
     * @param string $targetPath Путь до расположения файлов
     */
    protected function setTargetPath(string $targetPath): void
    {
        $this->targetPath = $targetPath;
    }

    /**
     * @return string Путь до папки расположения файлов
     */
    public function getFolderPath(): string
    {
        return $this->folderPath;
    }

    /**
     * Установить путь до папки расположения файлов
     *
     * @param string $folderPath Путь до папки расположения файлов
     */
    protected function setFolderPath(string $folderPath): void
    {
        $this->folderPath = $folderPath;
    }

    /**
     * Загрузка файла
     *
     * @param UploadedFile $file Uploaded File
     * @param string|null $subFolder Поддиректория
     * @return string Относительный путь до сохраненного файла
     */
    public function uploadFile(UploadedFile $file, ?string $subFolder = null): string
    {
        $uploadPath = $this->getFileUploadPath($subFolder);
        $name = $this->getFilenameByKey('original').'.'.$file->guessExtension();

        $file->move($this->getTargetPath().$uploadPath, $name);
        return $uploadPath."/".$name;
    }

    /**
     * Формат: /year.month/document_id/media_id/random.ext
     * @param string|null $subFolder Поддиректория
     * @return string Путь до папки загрузки файлов
     */
    protected function getFileUploadPath(?string $subFolder = null): string
    {
        $path = $this->getFolderPath().($subFolder ?? '');

        $year = base_convert(date("Y"), 10, 36);
        $month = base_convert(date("m"), 10, 36);
        $path .= "/{$year}{$month}";

        $this->fileSystem->mkdir($this->getTargetPath().$path);

        return $path;
    }

    /**
     * @param string $key Формат файла
     * @return string Название файла на основе его формата
     */
    protected function getFilenameByKey(string $key): string
    {
        return uniqid($key, true);
    }
}
