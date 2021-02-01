<?php
namespace App\Controller\Backend;

use App\Dto\Question\CategoryUpdateForm;
use App\Exception\AppException;
use App\Exception\ServiceException;
use App\Form\Question\CategoryCreateFormType;
use App\Form\Question\CategoryUpdateFormType;
use App\Form\Question\CategorySeachFormType;
use App\Service\Question\CategoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер управления категориями вопросов
 *
 * @Route("/question-category", name="category_")
 */
class CategoryController extends AppController
{
    /**
     * @inheritdoc
     */
    protected string $csrfTokenName = 'question-category';

    /**
     * @var CategoryService Question Category Service
     */
    private CategoryService $categoryService;

    /**
     * Конструктор
     *
     * @param CategoryService $categoryService
     */
    public function __construct(
        CategoryService $categoryService
    )
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Создание категории
     *
     * @Route("/create/", name="create")
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(CategoryCreateFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $category = $this->categoryService->create($form->getData());

                $this->addFlash('success', 'Категория успешно создана.');

                return $this->redirectToRoute('backend_category_view', ['id' => $category->getId()]);
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка. Попробуйте позже.");
            }
        }

        return $this->render('category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Листинг категорий
     *
     * @Route("/list/", name="list")
     *
     * @param Request $request
     * @return Response
     */
    public function list(Request $request): Response
    {
        $form = $this->createNamedForm('', CategorySeachFormType::class);
        $form->submit(array_diff_key($request->query->all(), array_flip(['page'])));
        $filters = $form->isSubmitted() && $form->isValid() ? (array) $form->getData() : [];

        try {
            $page = (int) $request->get('page', 1);
            $paginator = $this->categoryService->listing($form->getData(), $page);
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
            $paginator = null;
        }

        return $this->render('category/list.html.twig', [
            'filterForm' => $form->createView(),
            'filters' => $filters,
            'paginator' => $paginator,
        ]);
    }

    /**
     * Просмотр категории
     *
     * @Route("/view/{id<[1-9]\d*>}/", name="view")
     *
     * @param int $id Идентификатор категории
     * @return Response
     */
    public function view(int $id): Response
    {
        try {
            $category = $this->categoryService->getById($id);
        } catch (ServiceException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->render('category/view.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * Редактирование категории
     *
     * @Route("/update/{id<[1-9]\d*>}/", name="update")
     *
     * @param Request $request
     * @param int $id Идентификатор категории
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        try {
            $category = $this->categoryService->getById($id);
        } catch (ServiceException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $formData = new CategoryUpdateForm();
        $formData->id = $id;
        $formData->title = $category->getTitle();
        $formData->slug = $category->getSlug();

        $form = $this->createForm(CategoryUpdateFormType::class, $formData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $category = $this->categoryService->update($form->getData());

                $this->addFlash('success', 'Категория успешно обновлена.');

                return $this->redirectToRoute('backend_category_view', ['id' => $category->getId()]);
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка. Попробуйте позже.");
            }
        }

        return $this->render('category/update.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * Удаление категории
     *
     * @Route("/delete/{id<[1-9]\d*>}/", methods="POST", name="delete")
     *
     * @param Request $request
     * @param int $id Идентификатор категории
     * @return Response
     */
    public function delete(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->categoryService->delete($id);

            $this->addFlash('success', 'Категория успешно удалена!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при удалении. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_category_view', ['id' => $id]);
    }

    /**
     * Восстановление категории
     *
     * @Route("/restore/{id<[1-9]\d*>}/", methods="POST", name="restore")
     *
     * @param Request $request
     * @param int $id Идентификатор категории
     * @return Response
     */
    public function restore(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->categoryService->restore($id);

            $this->addFlash('success', 'Категория успешно восстановлена!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при восстановлении. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_category_view', ['id' => $id]);
    }
}
