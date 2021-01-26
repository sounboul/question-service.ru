<?php
namespace App\Controller\Frontend;

use App\Exception\AppException;
use App\Form\User\ChangePasswordFormType;
use App\Form\User\ProfileFormType;
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
    public function __construct(UserService $userService)
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
        $form = $this->createForm(ProfileFormType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->updateProfile(
                    $this->getUser()->getEmail(),
                    $form->get('username')->getData(),
                    $form->get('about')->getData(),
                    $request->files->get('profile_form')['photo'] ?? null
                );

                $this->addFlash('success', 'Профиль был успешно сохранен!');

                return $this->redirectToRoute('frontend_profile_index');
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка при сохранении профиля. Попробуйте позже.");
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
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->changePassword($this->getUser()->getEmail(), $form->get('plainPassword')->getData());

                $this->addFlash('success', 'Пароль был успешно изменен!');

                return $this->redirectToRoute('frontend_profile_change_password');
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка при изменении пароля. Попробуйте позже.");
            }
        }

        return $this->render('profile/change-password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }
}
