<?php
namespace App\Controller\Backend;

use App\Exception\AppException;
use App\Exception\ServiceException;
use App\Form\Backend\UserSearchFormType;
use App\Form\User\Backend\RegistrationFormType;
use App\Form\User\Backend\UserUpdateFormType;
use App\Service\User\UserPhotoService;
use App\Service\User\UserService;
use App\Utils\User\PasswordGenerator;
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
     * @var UserPhotoService User Photo Service
     */
    private UserPhotoService $userPhotoService;

    /**
     * Конструктор
     *
     * @param UserService $userService
     * @param UserPhotoService $userPhotoService
     */
    public function __construct(
        UserService $userService,
        UserPhotoService $userPhotoService
    )
    {
        $this->userService = $userService;
        $this->userPhotoService = $userPhotoService;
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
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $this->userService->fastRegistration($form->get('email')->getData());

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
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        } else {
            $filters = [];
        }

        try {
            $page = (int) $request->get('page', 1);
            $paginator = $this->userService->listing($filters, [], $page);
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
     * @param Request $request
     * @param int $id Идентификатор пользователя
     * @return Response
     * @throws ServiceException
     */
    public function view(Request $request, int $id): Response
    {
        $user = $this->userService->getUserById($id, false);
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
     * @throws ServiceException
     */
    public function update(Request $request, int $id): Response
    {
        $user = $this->userService->getUserById($id);

        $form = $this->createForm(UserUpdateFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $this->userService->getUserById($this->getUser()->getId());

                $user->setEmail($form->get('email')->getData());
                $user->setUsername($form->get('username')->getData());
                $user->setAbout($form->get('about')->getData());

                $photo = $request->files->get('profile_form')['photo'] ?? null;
                if (!empty($photo)) {
                    $user->setPhoto($this->userPhotoService->uploadPhoto($photo, $user));
                }

                $this->userService->updateUser($user);

                $this->addFlash('success', 'Профиль был успешно сохранен!');

                return $this->redirectToRoute('backend_user_view', ['id' => $id]);
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка при сохранении профиля пользователя. Попробуйте позже.");
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
            $this->userService->deleteUser($id);

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
            $this->userService->restoreUser($id);

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
            $this->userService->blockedUser($id);

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
            $password = PasswordGenerator::generate();
            $this->userService->changePassword($id, $password);

            $this->addFlash('success', "Пароль изменен! Новый пароль: '{$password}'");
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при изменении пароля пользователю. Попробуйте позже.");
        }

        return $this->redirectToRoute('backend_user_view', ['id' => $id]);
    }
}
