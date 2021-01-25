<?php
namespace App\Service\FileManager;

use App\Exception\ServiceException;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Сервис для управления загрузкой изображений
 */
class ImageManagerService extends FileManagerService
{
    /**
     * @var Imagine Imagine
     */
    private Imagine $imagine;

    /**
     * @inheritdoc
     */
    public function __construct(string $targetPath, string $folderPath, Filesystem $fileSystem)
    {
        parent::__construct($targetPath, $folderPath, $fileSystem);

        $this->imagine = new Imagine();
    }

    /**
     * Создание множества thumbnail на основе оригинального изображения
     *
     * @param string $originalPath Путь до оригинальнго изображения
     * @param array $configs Список и параметры нарезки изображений
     * @param string|null $subFolder Поддиректория
     * @return array Список готовых изображений
     * @throws ServiceException
     */
    public function createManyThumbnails(string $originalPath, array $configs, ?string $subFolder = null): array
    {
        $items = [];
        foreach ($configs as $config) {
            if (empty($config['key'])) {
                throw new ServiceException("Отсутствует обязательный атрибут 'key' у thumbnail конфигурации");
            }

            $items[$config['key']] = $this->createThumbnail($originalPath, $config, $subFolder);
        }

        return $items;
    }

    /**
     * Создание одного thumbnail на основе оригинального изображения
     *
     * @param string $originalPath Путь до оригинальнго изображения
     * @param array $config Thumbnail конфигурация
     * @param string|null $subFolder Поддиректория
     * @return string
     * @throws ServiceException
     */
    public function createThumbnail(string $originalPath, array $config, ?string $subFolder = null): string
    {
        if (empty($config['key'])) {
            throw new ServiceException("Отсутствует обязательный атрибут 'key' у thumbnail конфигурации");
        }

        $image = $this->imagine->open($this->getTargetPath().$originalPath);
        $imageExtension = pathinfo($originalPath, PATHINFO_EXTENSION);

        $thumbnailPath = $this->getFileUploadPath($subFolder);
        $thumbnailName = $this->getFilenameByKey($config['key']).".".$imageExtension;

        $width = $config['width'] ?? 0;
        $height = $config['height'] ?? 0;
        $quality = $config['quality'] ?? 100;
        $mode = $config['mode'] ?? ManipulatorInterface::THUMBNAIL_OUTBOUND;
        $optimize = $config['optimize'] ?? true;

        // формирование и сохранение thumbnail

        $thumbnail = $image->thumbnail(new Box($width, $height), $mode);
        $thumbnail->save($this->getTargetPath().$thumbnailPath."/".$thumbnailName, ['quality' => $quality]);

        // оптимизация изображения
        if ($optimize) {
            $optimizerChain = OptimizerChainFactory::create();
            $optimizerChain->optimize($this->getTargetPath().$thumbnailPath."/".$thumbnailName);
        }

        return $thumbnailPath."/".$thumbnailName;
    }
}
