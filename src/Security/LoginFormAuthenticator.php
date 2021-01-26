<?php
namespace App\Security;

use App\Entity\User\User;
use App\Exception\ServiceException;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Сервис, который отвечает за авторизацию через форму авторизации
 */
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    /**
     * @var UrlGeneratorInterface Url Generator
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @var UserService User Service
     */
    private UserService $userService;

    /**
     * Конструктор класса
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserService $userService
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, UserService $userService)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userService = $userService;
    }

    /**
     * @inheritdoc
     */
    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function (string $email) {
                try {
                    return $this->userService->getUserByEmail($email);
                } catch (ServiceException $e) {
                    return null;
                }
            }),

            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('frontend_homepage'));
    }

    /**
     * @inheritdoc
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('frontend_login');
    }
}
