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
     * @Route("/home/", name="homepage")
     */
    public function index(): Response
    {
        return $this->render('site/index.html.twig');
    }
}
