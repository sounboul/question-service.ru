<?php
namespace App\Controller\Backend;

use App\Dto\Question\QuestionUpdateForm;
use App\Exception\AppException;
use App\Exception\ServiceException;
use App\Dto\Question\QuestionCreateForm;
use App\Form\Question\QuestionCreateFormType;
use App\Form\Question\QuestionSearchFormType;
use App\Form\Question\QuestionUpdateFormType;
use App\Service\Question\QuestionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Question\CategoryService;

/**
 * Контроллер управления вопросами
 *
 * @Route("/question", name="question_")
 */
class QuestionController extends AppController
{
    /**
     * @inheritdoc
     */
    protected string $csrfTokenName = 'question';

    /**
     * @var QuestionService Question Service
     */
    private QuestionService $questionService;

    /**
     * @var CategoryService Category Service
     */
    private CategoryService $categoryService;

    /**
     * Конструктор
     *
     * @param QuestionService $questionService Question Service
     * @param CategoryService $categoryService Category Service
     */
    public function __construct(
        QuestionService $questionService,
        CategoryService $categoryService
    )
    {
        $this->questionService = $questionService;
        $this->categoryService = $categoryService;
    }

    /**
     * Создание вопроса
     *
     * @Route("/create/", name="create")
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(QuestionCreateFormType::class, null, ['categoryService' => $this->categoryService]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /* @var QuestionCreateForm $formData */
                $formData = $form->getData();
                $formData->userId = $this->getUser()->getId();
                $formData->createdByIp = $request->getClientIp();

                $question = $this->questionService->create($formData);

                $this->addFlash('success', 'Вопрос успешно создан');

                return $this->redirectToRoute('backend_question_view', ['id' => $question->getId()]);
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка. Попробуйте позже.");
            }
        }

        return $this->render('question/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Листинг вопросов
     *
     * @Route("/list/", name="list")
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function list(Request $request): Response
    {
        $form = $this->createNamedForm('', QuestionSearchFormType::class, null, ['categoryService' => $this->categoryService]);
        $form->submit(array_diff_key($request->query->all(), array_flip(['page'])));
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = (array) $form->getData();
        } else {
            $filters = [];
        }

        try {
            $page = (int) $request->get('page', 1);
            $paginator = $this->questionService->listing($form->getData(), $page);
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
            $paginator = null;
        }

        return $this->render('question/list.html.twig', [
            'filterForm' => $form->createView(),
            'filters' => $filters,
            'paginator' => $paginator,
        ]);
    }

    /**
     * Просмотр вопроса
     *
     * @Route("/view/{id<[1-9]\d*>}/", name="view")
     *
     * @param int $id Идентификатор вопроса
     * @return Response
     */
    public function view(int $id): Response
    {
        try {
            $question = $this->questionService->getById($id);
        } catch (ServiceException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->render('question/view.html.twig', [
            'question' => $question,
        ]);
    }

    /**
     * Редактирование вопроса
     *
     * @Route("/update/{id<[1-9]\d*>}/", name="update")
     *
     * @param Request $request
     * @param int $id Идентификатор вопроса
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        try {
            $question = $this->questionService->getById($id);
        } catch (ServiceException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $formData = new QuestionUpdateForm();
        $formData->categoryId = $question->getCategory()->getId();
        $formData->title = $question->getTitle();
        $formData->text = $question->getText();
        $formData->slug = $question->getSlug();

        $form = $this->createForm(QuestionUpdateFormType::class, $formData, ['categoryService' => $this->categoryService]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $question = $this->questionService->update($id, $form->getData());

                $this->addFlash('success', 'Вопрос успешно обновлён!');

                return $this->redirectToRoute('backend_question_view', ['id' => $question->getId()]);
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка. Попробуйте позже.");
            }
        }

        return $this->render('question/update.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
        ]);
    }

    /**
     * Удаление вопроса
     *
     * @Route("/delete/{id<[1-9]\d*>}/", methods="POST", name="delete")
     *
     * @param Request $request
     * @param int $id Идентификатор вопроса
     * @return Response
     */
    public function delete(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->questionService->delete($id);

            $this->addFlash('success', 'Вопрос успешно удален!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при удалении. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_question_view', ['id' => $id]);
    }

    /**
     * Восстановление вопроса
     *
     * @Route("/restore/{id<[1-9]\d*>}/", methods="POST", name="restore")
     *
     * @param Request $request
     * @param int $id Идентификатор вопроса
     * @return Response
     */
    public function restore(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->questionService->restore($id);

            $this->addFlash('success', 'Вопрос успешно восстановлен!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при восстановлении. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_question_view', ['id' => $id]);
    }
}
