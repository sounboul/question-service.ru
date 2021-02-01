<?php
namespace App\Controller\Backend;

use App\Dto\User\UserChangePasswordForm;
use App\Dto\User\UserUpdateForm;
use App\Exception\AppException;
use App\Exception\ServiceException;
use App\Form\User\UserSearchFormType;
use App\Form\User\UserFastRegistrationFormType;
use App\Form\User\UserUpdateFormType;
use App\Service\User\UserService;
use App\Utils\User\PasswordGenerator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контроллер управления пользователями
 *
 * @Route("/user", name="user_")
 */
final class UserController extends AppController
{
    /**
     * @inheritdoc
     */
    protected string $csrfTokenName = 'users';

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * Конструктор
     *
     * @param UserService $userService
     */
    public function __construct(
        UserService $userService
    )
    {
        $this->userService = $userService;
    }

    /**
     * Регистрация пользователя
     *
     * @Route("/registration/", name="registration")
     *
     * @param Request $request
     * @return Response
     */
    public function registration(Request $request): Response
    {
        $form = $this->createForm(UserFastRegistrationFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $this->userService->fastRegistration($form->getData());

                $this->addFlash('success', 'Пользователь успешно зарегистрирован. Пароль отправлен на почту вместе с письмом о подтверждении e-mail адреса.');

                return $this->redirectToRoute('backend_user_view', ['id' => $user->getId()]);
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка при регистрации. Попробуйте позже.");
            }
        }

        return $this->render('user/registration.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Листинг пользоваталей
     *
     * @Route("/list/", name="list")
     *
     * @param Request $request
     * @return Response
     */
    public function list(Request $request): Response
    {
        $form = $this->createNamedForm('', UserSearchFormType::class);
        $form->submit(array_diff_key($request->query->all(), array_flip(['page'])));
        $filters = $form->isSubmitted() && $form->isValid() ? (array) $form->getData() : [];

        try {
            $page = (int) $request->get('page', 1);
            $paginator = $this->userService->listing($form->getData(), $page);
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
            $paginator = null;
        }

        return $this->render('user/list.html.twig', [
            'filterForm' => $form->createView(),
            'filters' => $filters,
            'paginator' => $paginator,
        ]);
    }

    /**
     * Просмотр пользователя
     *
     * @Route("/view/{id<[1-9]\d*>}/", name="view")
     *
     * @param int $id Идентификатор пользователя
     * @return Response
     */
    public function view(int $id): Response
    {
        try {
            $user = $this->userService->getUserById($id, false);
        } catch (ServiceException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->render('user/view.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Редактирование пользователя
     *
     * @Route("/update/{id<[1-9]\d*>}/", name="update")
     *
     * @param Request $request
     * @param int $id Идентификатор пользователя
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        try {
            $user = $this->userService->getUserById($id);
        } catch (ServiceException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $formData = new UserUpdateForm();
        $formData->id = $user->getId();
        $formData->email = $user->getEmail();
        $formData->username = $user->getUsername();
        $formData->about = $user->getAbout();
        $formData->roles = $user->getRoles();

        $form = $this->createForm(UserUpdateFormType::class, $formData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->update($form->getData());

                $this->addFlash('success', 'Пользователь был успешно сохранен!');

                return $this->redirectToRoute('backend_user_view', ['id' => $id]);
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка при сохранении пользователя. Попробуйте позже.");
            }
        }

        return $this->render('user/update.html.twig', [
            'profileForm' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Удаление пользователя
     *
     * @Route("/delete/{id<[1-9]\d*>}/", methods="POST", name="delete")
     *
     * @param Request $request
     * @param int $id Идентификатор пользователя
     * @return Response
     */
    public function delete(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->userService->delete($id);

            $this->addFlash('success', 'Пользователь успешно удален!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при удалении пользователя. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_user_view', ['id' => $id]);
    }

    /**
     * Восстановление пользователя
     *
     * @Route("/restore/{id<[1-9]\d*>}/", methods="POST", name="restore")
     *
     * @param Request $request
     * @param int $id Идентификатор пользователя
     * @return Response
     */
    public function restore(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->userService->restore($id);

            $this->addFlash('success', 'Пользователь успешно восстановлен!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при восстановлении. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_user_view', ['id' => $id]);
    }

    /**
     * Блокировка пользователя
     *
     * @Route("/blocked/{id<[1-9]\d*>}/", methods="POST", name="blocked")
     *
     * @param Request $request
     * @param int $id Идентификатор пользователя
     * @return Response
     */
    public function blocked(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $this->userService->blocked($id);

            $this->addFlash('success', 'Пользователь успешно заблокирован!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при блокировки пользователя. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_user_view', ['id' => $id]);
    }

    /**
     * Изменение пароля пользователю
     *
     * @Route("/change-password/{id<[1-9]\d*>}/", methods="POST", name="change_password")
     *
     * @param Request $request
     * @param int $id Идентификатор пользователя
     * @return Response
     */
    public function changePassword(Request $request, int $id): Response
    {
        $this->checkCsrfToken($request);

        try {
            $formData = new UserChangePasswordForm();
            $formData->id = $id;
            $formData->password = PasswordGenerator::generate();

            $this->userService->changePassword($formData);

            $this->addFlash('success', "Пароль изменен! Новый пароль: '{$formData->password}'");
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при изменении пароля пользователю. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_user_view', ['id' => $id]);
    }
}
