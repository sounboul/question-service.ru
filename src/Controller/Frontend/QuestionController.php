<?php
namespace App\Controller\Frontend;

use App\Dto\Question\AnswerSearchForm;
use App\Dto\Question\QuestionCreateForm;
use App\Dto\QuestionElastic\SimpleSearchForm;
use App\Entity\Question\Category;
use App\Exception\AppException;
use App\Form\Question\AnswerCreateFormType;
use App\Form\Question\QuestionCreateFormType;
use App\Service\Question\AnswerService;
use App\Service\Question\CategoryService;
use App\Service\Question\QuestionSearch;
use App\Service\Question\QuestionService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use App\Exception\ServiceException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контроллер для работы с вопросами и ответами
 *
 * @Route("", name="question_")
 */
final class QuestionController extends AppController
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
     * @var AnswerService Answer Service
     */
    private AnswerService $answerService;

    /**
     * @var QuestionSearch Question Search
     */
    private QuestionSearch $questionSearch;

    /**
     * Конструктор
     *
     * @param CategoryService $categoryService
     * @param QuestionService $questionService
     * @param AnswerService $answerService
     * @param QuestionSearch $questionSearch
     */
    public function __construct(
        CategoryService $categoryService,
        QuestionService $questionService,
        AnswerService $answerService,
        QuestionSearch $questionSearch
    )
    {
        $this->categoryService = $categoryService;
        $this->questionService = $questionService;
        $this->answerService = $answerService;
        $this->questionSearch = $questionSearch;
    }

    /**
     * Создание вопроса
     *
     * @Route("/q/create/", name="create")
     * @param Request $request
     * @param RateLimiterFactory $questionAddLimiter
     * @return Response
     */
    public function create(
        Request $request,
        RateLimiterFactory $questionAddLimiter
    ): Response
    {
        $form = $this->createForm(QuestionCreateFormType::class, null, [
            'categoryService' => $this->categoryService,
            'recaptcha' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Rate Limiter (на основе IP адреса)
            $limiter = $questionAddLimiter->create($request->getClientIp());
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }

            try {
                /* @var QuestionCreateForm $formData */
                $formData = $form->getData();
                if (!empty($this->getUser())) {
                    $formData->userId = $this->getUser()->getId();
                }
                $formData->createdByIp = $request->getClientIp();

                $question = $this->questionService->create($formData);

                return $this->redirectToRoute('frontend_question_view', [
                        'id' => $question->getId(),
                        'slug' => $question->getSlug(),
                    ]);
            } catch (AppException $e) {
                return $this->renderError(true, $e);
            }
        }

        return $this->render('question/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Листинг вопросов с фильтрацией по категориям
     *
     * @Route("/", defaults={"category_slug" : ""}, methods="GET", name="index")
     * @Route("/category/{category_slug}/", methods="GET", name="category")
     *
     * @param Request $request
     * @param string $category_slug Slug категории
     * @return Response
     */
    public function index(
        Request $request,
        string $category_slug = ''
    ): Response
    {
        if (!empty($category_slug)) {
            try {
                $category = $this->categoryService->getBySlug($category_slug);
                if ($category->isDeleted()) {
                    throw new HttpException(410, "Категория была удалена");
                }
            } catch (ServiceException $e) {
                throw new NotFoundHttpException("Категория не найдена");
            }
        } else {
            $category = null;
        }

        $form = new SimpleSearchForm();
        $form->page = $request->get('page', 1);
        if (!empty($category)) {
            $form->categoryId = $category->getId();
        }

        try {
            $search = $this->questionSearch->simple($form);
            $data = [
                'page' => $form->page,
                'category' => $category,

                'total' => $search->getTotalHits(),
                'items' => $search->getResults(),

                'nextPage' => null,
            ];

            if ($data['total'] > $form->page * $form->pageSize) {
                $data['nextPage'] = $this->generateUrl(
                    !empty($category_slug) ? 'frontend_question_category' : 'frontend_question_index',
                    [
                        'page' => $form->page + 1,
                        'category_slug' => $category_slug,
                    ],
                );
            }

            if ($form->page == 1) {
                if (!empty($category)) {
                    return $this->render('question/category.html.twig', $data);
                } else {
                    return $this->render('question/index.html.twig', $data);
                }
            } else {
                return $this->render('components/questions-cards.html.twig', $data);
            }
        } catch (ServiceException $e) {
            return $this->renderError($form->page == 1, $e);
        }
    }

    /**
     * Поиск вопросов с фильтрацией по категориям
     *
     * @Route("/search/", methods="GET", name="search")
     *
     * @param RateLimiterFactory $questionSearchLimiter
     * @param Request $request
     * @return Response
     */
    public function search(
        RateLimiterFactory $questionSearchLimiter,
        Request $request
    ): Response
    {
        // Rate Limiter (на основе IP адреса)
        $limiter = $questionSearchLimiter->create($request->getClientIp());
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        // Обработка поисковой формы
        $form = new SimpleSearchForm();
        $form->page = $request->get('page', 1);
        $form->query = $request->get('query');
        $form->categoryId = (int) $request->get('categoryId');

        try {
            $form->query = trim(strip_tags($form->query));
            if (empty($form->query)) {
                throw new ServiceException("Задан пустой поисковой запрос");
            }

            if (!empty($form->categoryId)) {
                $category = $this->categoryService->getById($form->categoryId);
                if (!$category->isActive()) {
                    throw new ServiceException("Неверно указана категория");
                }
            }

            $search = $this->questionSearch->simple($form);
            $data = [
                'page' => $form->page,

                'query' => $form->query,
                'category' => $category ?? null,

                'total' => $search->getTotalHits(),
                'items' => $search->getResults(),

                'nextPage' => null,
            ];

            if ($data['total'] > $form->page * $form->pageSize) {
                $data['nextPage'] = $this->generateUrl(
                    'frontend_question_search',
                    [
                        'page' => $form->page + 1,
                        'query' => $form->query,
                        'categoryId' => $form->categoryId,
                    ],
                );
            }

            if ($form->page == 1) {
                return $this->render('question/search.html.twig', $data);
            } else {
                return $this->render('components/questions-cards.html.twig', $data);
            }
        } catch (ServiceException $e) {
            return $this->renderError($form->page == 1, $e);
        }
    }

    /**
     * Просмотр одного вопроса
     *
     * @Route("/q/{id<[1-9]\d*>}_{slug}/", defaults={"page": "1"}, name="view")
     * @Route("/q/{id<[1-9]\d*>}_{slug}/{page<[1-9]\d*>}/", name="view_paginated")
     *
     * @param Request $request
     * @param RateLimiterFactory $questionAnswerAddLimiter
     * @param int $id Идентификатор вопроса
     * @param string $slug Slug вопроса
     * @param int $page Номер страницы
     * @return Response
     * @throws ServiceException
     */
    public function view(
        Request $request,
        RateLimiterFactory $questionAnswerAddLimiter,
        int $id,
        string $slug,
        int $page
    ): Response
    {
        // поиск вопроса
        try {
            $question = $this->questionService->getById($id);
            if ($question->isDeleted()) {
                throw new HttpException(410, "Вопрос был удалён");
            }

            // редирект на правильный URL
            $questionHref = $page > 1 ? $question->getHref().$page.'/' : $question->getHref();
            $currentHref = str_replace('?'.$request->getQueryString(), '', $request->getRequestUri());
            if ($questionHref != $currentHref) {
                return $this->redirect($questionHref);
            }
        } catch (ServiceException $e) {
            throw new NotFoundHttpException("Вопрос не найден");
        }

        // редирект на правильную страницу
        $page = max(1, $page);
        $pageSize = 20;
        $maxPages = max(1, (int) ceil($question->getTotalAnswers() / $pageSize));
        if ($page > $maxPages) {
            return $this->redirectToRoute('frontend_question_view_paginated', [
                'id' => $id,
                'slug' => $slug,
                'page' => $maxPages,
            ]);
        }

        // добавление нового ответа к вопросу
        $createForm = $this->createForm(AnswerCreateFormType::class, null, ['recaptcha' => true]);
        $createForm->handleRequest($request);
        if ($createForm->isSubmitted() && $createForm->isValid()) {
            // Rate Limiter (на основе IP адреса)
            $limiter = $questionAnswerAddLimiter->create($request->getClientIp());
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }

            try {
                $formData = $createForm->getData();
                $formData->questionId = $id;
                if (!empty($this->getUser())) {
                    $formData->userId = $this->getUser()->getId();
                }
                $formData->createdByIp = $request->getClientIp();

                $answer = $this->answerService->create($formData);
                return $this->redirect($question->getHref().'#answer-'.$answer->getId());
            } catch (AppException $e) {
                return $this->renderError(true, $e);
            }
        }

        // листинг ответов к вопросу
        $formData = new AnswerSearchForm();
        $formData->questionId = $question->getId();
        $answers = $this->answerService->listing($formData, $page, $pageSize);

        return $this->render('question/view.html.twig', [
            'createForm' => $createForm->createView(),
            'question' => $question,
            'answers' => $answers,
            'filters' => [
                'id' => $id,
                'slug' => $slug,
            ],
        ]);
    }

    /**
     * Виджет списка категорий с различными представлениями
     *
     * @param string $view Представление виджета
     * @return Response
     */
    public function categoriesWidget(string $view): Response
    {
        if (!in_array($view, ['categories-sidebar', 'categories-search-form'])) {
            throw new BadRequestException("Некорректный шаблон виджета '{$view}'");
        }

        $categories = array_map(function (Category $category) {
            return [
                'id' => $category->getId(),
                'title' => $category->getTitle(),
                'href' => $category->getHref(),
            ];
        }, $this->categoryService->getActiveCategories());

        $response = $this->render('widgets/'.$view.'.html.twig', compact('categories'));
        $response->setMaxAge(3600);

        return $response;
    }
}
