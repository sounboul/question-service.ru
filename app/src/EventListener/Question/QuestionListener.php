<?php
namespace App\EventListener\Question;

use App\Entity\Question\Question;
use App\Elasticsearch\Model\Question as ElasticQuestion;
use App\Service\PurgeVarnishCache;
use App\Service\Question\CategoryService;
use App\Service\Question\QuestionService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use JoliCode\Elastically\Messenger\IndexationRequest;
use JoliCode\Elastically\Messenger\IndexationRequestHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Модель событий для сущности "Вопрос"
 */
class QuestionListener
{
    /**
     * @var CategoryService Category Service
     */
    private CategoryService $categoryService;

    /**
     * @var QuestionService Question Service
     */
    private QuestionService $questionService;

    /**
     * @var UrlGeneratorInterface Url Generator
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @var PurgeVarnishCache Purse Varnish Cache
     */
    private PurgeVarnishCache $purgeVarnishCache;

    /**
     * @var MessageBusInterface Bus
     */
    private MessageBusInterface $bus;

    /**
     * Конструктор
     *
     * @param CategoryService $categoryService Category Service
     * @param QuestionService $questionService Question Service
     * @param UrlGeneratorInterface $urlGenerator Url Generator
     * @param PurgeVarnishCache $purgeVarnishCache Purse Varnish Cache
     * @param MessageBusInterface $bus Bus
     */
    public function __construct(
        CategoryService $categoryService,
        QuestionService $questionService,
        UrlGeneratorInterface $urlGenerator,
        PurgeVarnishCache $purgeVarnishCache,
        MessageBusInterface $bus
    )
    {
        $this->categoryService = $categoryService;
        $this->questionService = $questionService;
        $this->urlGenerator = $urlGenerator;
        $this->purgeVarnishCache = $purgeVarnishCache;
        $this->bus = $bus;
    }

    /**
     * Событие, которое вызвано после создания вопроса
     *
     * @param Question $question
     * @param LifecycleEventArgs $eventArgs
     * @throws \App\Exception\ServiceException
     */
    public function postPersist(Question $question, LifecycleEventArgs $eventArgs)
    {
        // Сразу после создания вопроса установим href
        $question->setHref($this->generateHrefQuestion($question->getId(), $question->getSlug()));

        // сохранение изменений
        $em = $eventArgs->getObjectManager();
        $em->persist($question);
        $em->flush();

        // необходимо пересчитать количество вопросов в категории
        $this->recountQuestionsInCategory($question->getCategory()->getId());

        // обновить поисковой индекс
        $this->searchIndexUpdate($question);

        // Инвалидация кэша
        $this->invalidateVarnishCache($question);
    }

    /**
     * Событие, которое вызвано до обновления вопроса
     *
     * @param Question $question
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(Question $question, LifecycleEventArgs $eventArgs)
    {
        // Если был изменен slug, то обновим href
        if ($eventArgs->hasChangedField('slug')) {
            $question->setHref($this->generateHrefQuestion($question->getId(), $eventArgs->getNewValue('slug')));
        }
    }

    /**
     * Событие, которое вызвано после обновления вопроса
     *
     * @param Question $question
     * @param LifecycleEventArgs $eventArgs
     * @throws \App\Exception\ServiceException
     */
    public function postUpdate(Question $question, LifecycleEventArgs $eventArgs)
    {
        // необходимо пересчитать количество вопросов в категории
        $this->recountQuestionsInCategory($question->getCategory()->getId());
        // @TODO если категория изменена, то пересчитать нужно в обоих

        // обновить поисковой индекс
        $this->searchIndexUpdate($question);

        // Инвалидация кэша
        $this->invalidateVarnishCache($question);
    }

    /**
     * Обновление количества вопросов в категории
     *
     * @param int $categoryId Идентификатор категории
     * @return void
     * @throws \App\Exception\ServiceException
     */
    private function recountQuestionsInCategory(int $categoryId)
    {
        $count = $this->questionService->countQuestionsByCategoryId($categoryId);
        $this->categoryService->updateTotalQuestions($categoryId, $count);
    }

    /**
     * @param int $id Идентификатор вопроса
     * @param string $slug Slug вопроса
     * @return string Ссылка на вопрос
     */
    private function generateHrefQuestion(int $id, string $slug): string
    {
        return $this->urlGenerator->generate('frontend_question_view', ['id' => $id, 'slug' => $slug]);
    }

    /**
     * Обновить поисковой индекс вопросов
     *
     * @param Question $question
     * @return void
     */
    private function searchIndexUpdate(Question $question): void
    {
        if ($question->isActive()) {
            $request = new IndexationRequest(ElasticQuestion::class, $question->getId());
        } else {
            $request = new IndexationRequest(ElasticQuestion::class, $question->getId(), IndexationRequestHandler::OP_DELETE);
        }

        $this->bus->dispatch($request);
    }

    /**
     * Инвалидация Varnish кэша
     *
     * @param Question $question
     * @return void
     */
    private function invalidateVarnishCache(Question $question)
    {
        $this->purgeVarnishCache->invalidateTags(['questions' => [$question->getId(), 'listing']]);
    }
}
