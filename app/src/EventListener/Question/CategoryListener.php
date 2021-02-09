<?php
namespace App\EventListener\Question;

use App\Entity\Question\Category;
use App\Service\PurgeVarnishCache;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use \Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Модель событий для сущности "Категория вопросов"
 */
class CategoryListener
{
    /**
     * @var UrlGeneratorInterface Url Generator
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @var PurgeVarnishCache Purse Varnish Cache
     */
    private PurgeVarnishCache $purgeVarnishCache;

    /**
     * Конструктор
     *
     * @param UrlGeneratorInterface $urlGenerator Url Generator
     * @param PurgeVarnishCache $purgeVarnishCache Purse Varnish Cache
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        PurgeVarnishCache $purgeVarnishCache
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->purgeVarnishCache = $purgeVarnishCache;
    }

    /**
     * Событие, которое вызвано после создания категории.
     *
     * @param Category $category
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(Category $category, LifecycleEventArgs $eventArgs)
    {
        // Сразу после создания категории установим href
        // Делаю это в postPersist, на тот случай, если в URL категории вдруг войдёт ID
        $category->setHref($this->generateHrefCategory($category->getSlug()));

        $em = $eventArgs->getObjectManager();
        $em->persist($category);
        $em->flush();

        // Инвалидация кэша
        $this->invalidateVarnishCache($category);
    }

    /**
     * Событие, которое вызвано до обновления категории.
     *
     * @param Category $category
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(Category $category, LifecycleEventArgs $eventArgs)
    {
        // Если был изменен slug, то обновим href
        if ($eventArgs->hasChangedField('slug')) {
            $category->setHref($this->generateHrefCategory($eventArgs->getNewValue('slug')));
        }
    }

    /**
     * Событие, которое вызвано после обновления категории
     *
     * @param Category $category
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(Category $category, LifecycleEventArgs $eventArgs)
    {
        // Инвалидация кэша
        $this->invalidateVarnishCache($category);
    }

    /**
     * @param string $slug Slug категории
     * @return string Ссылка на категорию
     */
    private function generateHrefCategory(string $slug): string
    {
        return $this->urlGenerator->generate('frontend_question_category', ['category_slug' => $slug]);
    }

    /**
     * Инвалидация Varnish кэша
     *
     * @param Category $category
     * @return void
     */
    private function invalidateVarnishCache(Category $category)
    {
        $this->purgeVarnishCache->invalidateTags(['categories' => [$category->getId(), 'listing']]);
    }
}
