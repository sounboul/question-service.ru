<?php
namespace App\Controller\Backend;

use App\Dto\Question\AnswerUpdateForm;
use App\Exception\AppException;
use App\Exception\ServiceException;
use App\Form\Question\AnswerSearchFormType;
use App\Form\Question\AnswerUpdateFormType;
use App\Service\Question\AnswerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер управления ответами к вопросам
 *
 * @Route("/answer", name="answer_")
 */
class AnswerController extends AppController
{
    /**
     * @inheritdoc
     */
    protected string $csrfTokenName = 'answer';

    /**
     * @var AnswerService Question Answer Service
     */
    private AnswerService $answerService;

    /**
     * Конструктор
     *
     * @param AnswerService $answerService
     */
    public function __construct(
        AnswerService $answerService
    )
    {
        $this->answerService = $answerService;
    }

    /**
     * Листинг ответов
     *
     * @Route("/list/", name="list")
     *
     * @param Request $request
     * @return Response
     */
    public function list(Request $request): Response
    {
        $form = $this->createNamedForm('', AnswerSearchFormType::class);
        $form->submit(array_diff_key($request->query->all(), array_flip(['page'])));
        $filters = $form->isSubmitted() && $form->isValid() ? (array) $form->getData() : [];

        try {
            $page = (int) $request->get('page', 1);
            $paginator = $this->answerService->listing($form->getData(), $page);
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
            $paginator = null;
        }

        return $this->render('answer/list.html.twig', [
            'filterForm' => $form->createView(),
            'filters' => $filters,
            'paginator' => $paginator,
        ]);
    }

    /**
     * Просмотр ответа
     *
     * @Route("/view/{id<[1-9]\d*>}/", name="view")
     *
     * @param int $id Идентификатор ответа
     * @return Response
     */
    public function view(int $id): Response
    {
        try {
            $answer = $this->answerService->getById($id);
        } catch (ServiceException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->render('answer/view.html.twig', [
            'answer' => $answer,
        ]);
    }

    /**
     * Редактирование ответа
     *
     * @Route("/update/{id<[1-9]\d*>}/", name="update")
     *
     * @param Request $request
     * @param int $id Идентификатор ответа
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        try {
            $answer = $this->answerService->getById($id);
        } catch (ServiceException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $formData = new AnswerUpdateForm();
        $formData->id = $id;
        $formData->text = $answer->getText();

        $form = $this->createForm(AnswerUpdateFormType::class, $formData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->answerService->update($form->getData());

                $this->addFlash('success', 'Ответ успешно обновлён');

                return $this->redirectToRoute('backend_answer_view', ['id' => $answer->getId()]);
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка. Попробуйте позже.");
            }
        }

        return $this->render('answer/update.html.twig', [
            'form' => $form->createView(),
            'answer' => $answer,
        ]);
    }

    /**
     * Удаление ответа
     *
     * @Route("/delete/{id<[1-9]\d*>}/", methods="POST", name="delete")
     *
     * @param Request $request
     * @param int $id Идентификатор ответа
     * @return Response
     */
    public function delete(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->answerService->delete($id);

            $this->addFlash('success', 'Ответ успешно удален!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при удалении. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_answer_view', ['id' => $id]);
    }

    /**
     * Восстановление ответа
     *
     * @Route("/restore/{id<[1-9]\d*>}/", methods="POST", name="restore")
     *
     * @param Request $request
     * @param int $id Идентификатор ответа
     * @return Response
     */
    public function restore(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->answerService->restore($id);

            $this->addFlash('success', 'Ответ успешно восстановлен!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при восстановлении. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_answer_view', ['id' => $id]);
    }
}
