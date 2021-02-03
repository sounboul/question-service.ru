<?php
namespace App\Controller\Frontend;

use App\Dto\QuestionElastic\SimpleSearchForm;
use App\Service\Question\CategoryService;
use App\Service\Question\QuestionSearch;
use App\Service\Question\QuestionService;
use Symfony\Component\HttpFoundation\Request;
use App\Exception\ServiceException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @var QuestionSearch Question Search
     */
    private QuestionSearch $questionSearch;

    /**
     * Конструктор
     *
     * @param CategoryService $categoryService
     * @param QuestionService $questionService
     * @param QuestionSearch $questionSearch
     */
    public function __construct(
        CategoryService $categoryService,
        QuestionService $questionService,
        QuestionSearch $questionSearch
    )
    {
        $this->categoryService = $categoryService;
        $this->questionService = $questionService;
        $this->questionSearch = $questionSearch;
    }

    /**
     * Листинг вопросов с фильтрацией по категориям
     *
     * @Route("/", defaults={"category_slug" : ""}, methods="GET", name="index")
     * @Route("/c/{category_slug}/", methods="GET", name="category")
     *
     * @param Request $request
     * @param string $category_slug Slug категории
     * @return Response
     * @throws ServiceException
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
            return $this->render('components/questions-listing.html.twig', $data);
        }
    }

    /**
     * Просмотр одного вопроса
     *
     * @Route("/q/{id<[1-9]\d*>}_{slug}/", defaults={"page": "1"}, methods="GET", name="view")
     * @Route("/q/{id<[1-9]\d*>}_{slug}/{page<[1-9]\d*>}/", methods="GET", name="view_paginated")
     *
     * @param Request $request
     * @param int $id Идентификатор вопроса
     * @param string $slug Slug вопроса
     * @param int $page Номер страницы
     * @return Response
     */
    public function view(Request $request, int $id, string $slug, int $page): Response
    {
        // @TODO
    }
}
