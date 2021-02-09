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
        $response = $this->render('site/about.html.twig');
        return $this->cachedByTag($response, 'page:about');
    }

    /**
     * Статическая страница: Контакты
     *
     * @Route("/contacts/", name="contacts", methods="GET")
     */
    public function contacts(): Response
    {
        $response = $this->render('site/contacts.html.twig');
        return $this->cachedByTag($response, 'page:contacts');
    }

    /**
     * Статическая страница: Правила
     *
     * @Route("/rules/", name="rules", methods="GET")
     */
    public function rules(): Response
    {
        $response = $this->render('site/rules.html.twig');
        return $this->cachedByTag($response, 'page:rules');
    }

    /**
     * Статическая страница: Реклама
     *
     * @Route("/advert/", name="advert", methods="GET")
     */
    public function advert(): Response
    {
        $response = $this->render('site/advert.html.twig');
        return $this->cachedByTag($response, 'page:advert');
    }
}
