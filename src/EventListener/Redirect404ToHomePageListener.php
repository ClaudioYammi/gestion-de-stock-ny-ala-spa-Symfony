<?php
namespace App\EventListener;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class Redirect404ToHomePageListener extends AbstractController
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Si l'exception n'est pas NotFoundHttpException, nous la laissons passer
        if (!($event->getThrowable() instanceof NotFoundHttpException)) {
            return;
        }

        // Obtenez l'URL de la page précédente
        $previousUrl = $event->getRequest()->headers->get('referer');

        // Si l'URL de la page précédente n'est pas définie ou est vide, redirigez vers la page d'accueil
        if ($previousUrl === null || $previousUrl === '') {
            $this->addFlash('error_404', 'La page n\'existe pas ou est inaccessible');
            $previousUrl = $this->router->generate('app_home');
        } else {
            // Vérifiez si l'URL de la page précédente est la même que l'URL actuelle pour éviter une redirection infinie
            $currentUrl = $event->getRequest()->getUri();
            if ($previousUrl === $currentUrl) {
                $previousUrl = $this->router->generate('app_home');
            }
        }

        // Redirige vers la page précédente
        $event->setResponse(new RedirectResponse($previousUrl));
    }
}