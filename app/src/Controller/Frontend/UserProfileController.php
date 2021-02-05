<?php
namespace App\Controller\Frontend;

use App\Dto\User\UserChangePasswordForm;
use App\Entity\User\User;
use App\Dto\User\UserUpdateProfileForm;
use App\Exception\AppException;
use App\Form\User\UserChangePasswordFormType;
use App\Form\User\UserUpdateProfileFormType;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контроллер для работы с профилем пользователя
 *
 * @Route("/profile", name="profile_")
 */
final class UserProfileController extends AppController
{
    /**
     * @var UserService User Service
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
     * Профиль пользователя
     *
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        /* @var User $user */
        $user = $this->getUser();

        $formData = new UserUpdateProfileForm();
        $formData->id = $user->getId();
        $formData->username = $user->getUsername();
        $formData->about = $user->getAbout();

        $form = $this->createForm(UserUpdateProfileFormType::class, $formData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->updateProfile($form->getData());

                $this->addFlash('success', 'Профиль обновлён!');

                return $this->redirectToRoute('frontend_profile_index');
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка. Попробуйте позже.");
            }
        }

        return $this->render('profile/index.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    /**
     * Изменить пароль
     *
     * @Route("/change-password/", name="change_password")
     * @param Request $request
     * @return Response
     */
    public function changePassword(Request $request): Response
    {
        /* @var User $user */
        $user = $this->getUser();

        $formData = new UserChangePasswordForm();
        $formData->id = $user->getId();

        $form = $this->createForm(UserChangePasswordFormType::class, $formData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->changePassword($form->getData());

                $this->addFlash('success', 'Пароль изменен!');

                return $this->redirectToRoute('frontend_profile_change_password');
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка. Попробуйте позже.");
            }
        }

        return $this->render('profile/change-password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }
}
