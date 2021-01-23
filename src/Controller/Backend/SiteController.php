<?php
namespace App\Controller\Backend;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Главный контроллер backend приложения
 */
final class SiteController extends AppController
{
    /**
     * Главная страница сайта
     *
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        return $this->render('site/index.html.twig');
    }
}
