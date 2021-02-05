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
     * Статическая страница: О проекте
     *
     * @Route("/about/", name="about", methods="GET")
     */
    public function about(): Response
    {
        return $this->render('site/about.html.twig');
    }

    /**
     * Статическая страница: Контакты
     *
     * @Route("/contacts/", name="contacts", methods="GET")
     */
    public function contacts(): Response
    {
        return $this->render('site/contacts.html.twig');
    }

    /**
     * Статическая страница: Правила
     *
     * @Route("/rules/", name="rules", methods="GET")
     */
    public function rules(): Response
    {
        return $this->render('site/rules.html.twig');
    }

    /**
     * Статическая страница: Реклама
     *
     * @Route("/advert/", name="advert", methods="GET")
     */
    public function advert(): Response
    {
        return $this->render('site/advert.html.twig');
    }
}
