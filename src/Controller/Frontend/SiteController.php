<?php
namespace App\Controller\Frontend;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Главный контроллер для frontend приложения
 */
final class SiteController extends AppController
{
    /**
     * Главная страница приложения
     *
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        return $this->render('site/index.html.twig');
    }

    /**
     * Статическая страница: О проекте
     *
     * @Route("/about/", name="about")
     */
    public function about(): Response
    {
        return $this->render('site/about.html.twig');
    }

    /**
     * Статическая страница: Контакты
     *
     * @Route("/contacts/", name="contacts")
     */
    public function contacts(): Response
    {
        return $this->render('site/contacts.html.twig');
    }

    /**
     * Статическая страница: Правила
     *
     * @Route("/rules/", name="rules")
     */
    public function rules(): Response
    {
        return $this->render('site/rules.html.twig');
    }

    /**
     * Статическая страница: Реклама
     *
     * @Route("/advert/", name="advert")
     */
    public function advert(): Response
    {
        return $this->render('site/advert.html.twig');
    }
}
