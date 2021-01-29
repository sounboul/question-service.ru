<?php
namespace App\Controller\Frontend;

use App\Service\Question\QuestionService;
use Symfony\Component\HttpFoundation\Request;
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
     * @var QuestionService Question Service
     */
    private QuestionService $questionService;

    /**
     * Конструктор
     *
     * @param QuestionService $questionService
     */
    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Листинг вопросов с фильтрацией по категориям
     *
     * @Route("/", defaults={"page": "1"}, methods="GET", name="index")
     * @Route("/page/{page<[1-9]\d*>}", methods="GET", name="index_paginated")
     * @Route("/{category_slug}/", defaults={"page": "1"}, methods="GET", name="index_category")
     * @Route("/{category_slug}/page/{page<[1-9]\d*>}/", methods="GET", name="index_category_paginated")
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // @TODO
    }

    /**
     * Просмотр одного вопроса
     *
     * @Route("/q/{id<[1-9]\d*>}_{slug}/", defaults={"page": "1"}, methods="GET", name="view")
     * @Route("/q/{id<[1-9]\d*>}_{slug}/{page<[1-9]\d*>}/", methods="GET", name="view_paginated")
     *
     * @param Request $request
     * @return Response
     */
    public function changePassword(Request $request, int $id, string $slug, int $page): Response
    {
        // @TODO
    }
}
