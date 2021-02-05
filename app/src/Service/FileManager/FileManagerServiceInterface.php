<?php
namespace App\Service\FileManager;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Интерфейс для сервиса управления загрузкой файлов
 */
interface FileManagerServiceInterface
{
    /**
     * @return string Путь до расположения файлов
     */
    public function getTargetPath(): string;

    /**
     * @return string Путь до папки расположения файлов
     */
    public function getFolderPath(): string;

    /**
     * Загрузка файла
     *
     * @param UploadedFile $file Uploaded File
     * @param string|null $subFolder Поддиректория
     * @return string Относительный путь до сохраненного файла
     */
    public function uploadFile(UploadedFile $file, ?string $subFolder = null): string;
}
