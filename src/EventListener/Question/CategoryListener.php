<?php
namespace App\EventListener\Question;

use App\Entity\Question\Category;
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
     * Конструктор
     *
     * @param UrlGeneratorInterface $urlGenerator Url Generator
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->urlGenerator = $urlGenerator;
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
            $eventArgs->setNewValue('href', $this->generateHrefCategory($eventArgs->getNewValue('slug')));
        }
    }

    /**
     * @param string $slug Slug категории
     * @return string Ссылка на категорию
     */
    private function generateHrefCategory(string $slug): string
    {
        return $this->urlGenerator->generate('frontend_question_index_category', ['category_slug' => $slug]);
    }
}
