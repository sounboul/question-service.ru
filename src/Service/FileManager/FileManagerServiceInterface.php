<?php
namespace App\Service\FileManager;

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
}
