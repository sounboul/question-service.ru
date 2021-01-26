<?php
namespace App\Controller\Frontend;

use App\Exception\AppException;
use App\Exception\ServiceException;
use App\Form\User\ChangePasswordFormType;
use App\Form\User\RegistrationFormType;
use App\Form\User\ResetPasswordRequestFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Контроллер для работы с пользователями
 *
 * @Route("/user")
 */
final class UserController extends AppController
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
     * Авторизация пользователя
     *
     * @Route("/login/", name="login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToAuthbox();
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Выход пользователя
     *
     * @Route("/logout/", name="logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Регистрация пользователя
     *
     * @Route("/registration/", name="registration")
     *
     * @param Request $request
     * @param LoginFormAuthenticator $loginFormAuthenticator
     * @param UserAuthenticatorInterface $userAuthenticator
     * @return Response
     */
    public function registration(
        Request $request,
        LoginFormAuthenticator $loginFormAuthenticator,
        UserAuthenticatorInterface $userAuthenticator
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToAuthbox();
        }

        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // регистрация
                $user = $this->userService->registration(
                    $form->get('email')->getData(),
                    $form->get('plainPassword')->getData()
                );

                // авторизация
                $userAuthenticator->authenticateUser($user, $loginFormAuthenticator, $request);

                $this->addFlash('success', 'Вы успешно зарегистрированы на сайте!');

                return $this->redirectToAuthbox();
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
     * Подтверждение E-mail адреса пользователя
     *
     * @Route("/email-verification/", name="email_verification")
     *
     * @param Request $request
     * @return Response
     */
    public function emailVerification(Request $request): Response
    {
        try {
            $this->userService->handleEmailConfirmation($request->get('token', ''));

            $this->addFlash('success', 'Ваш E-mail успешно подтвержден!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при подтверждении E-mail адреса. Попробуйте позже.");
        }

        return $this->redirectToAuthbox();
    }

    /**
     * Подтверждение подписки на E-mail рассылку
     *
     * @Route("/email-subscribed/", name="email_subscribed")
     *
     * @param Request $request
     * @return Response
     */
    public function emailSubscribed(Request $request): Response
    {
        try {
            $this->userService->handleEmailSubscribed($request->get('token', ''));

            $this->addFlash('success', 'Вы успешно подписаны на нашу рассылку!');
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при подтверждении подписки на нашу E-mail рассылку. Попробуйте позже.");
        }

        return $this->redirectToAuthbox();
    }

    /**
     * Запрос на восстановление пароля пользователю
     *
     * @Route("/forgot-password-request/", name="forgot_password_request")
     *
     * @param Request $request
     * @return Response
     */
    public function forgotPasswordRequest(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->forgotPasswordRequest($form->get('email')->getData());

                $this->addFlash('success', 'Мы на почту отправили Вам сообщение с ссылкой на восстановление пароля. Если вы его не получили, то посмотрите папку "спам", либо попробуйте еще раз.');

                return $this->redirectToAuthbox();
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка при запросе на восстановление пароля. Попробуйте позже.");
            }
        }

        return $this->render('user/forgot-password-request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Изменение пароля пользователю по ссылке на восстановление пароля
     *
     * @Route("/reset-password/", name="reset_password")
     *
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request) : Response
    {
        // проверка token
        try {
            $user = $this->userService->getUserByPasswordRestoreToken($request->get('token', ''));
        } catch (AppException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToAuthbox();
        } catch (\Exception $e) {
            $this->addFlash('error', "Произошла ошибка при сбросе пароля пользователю. Попробуйте позже.");
            return $this->redirectToAuthbox();
        }

        // отображение формы
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->resetPassword($user->getPasswordRestoreToken(), $form->get('plainPassword')->getData());

                $this->addFlash('success', 'Пароль был успешно изменен!');

                return $this->redirectToAuthbox();
            } catch (AppException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Произошла ошибка при сбросе пароля пользователю. Попробуйте позже.");
            }
        }

        return $this->render('user/reset-password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
