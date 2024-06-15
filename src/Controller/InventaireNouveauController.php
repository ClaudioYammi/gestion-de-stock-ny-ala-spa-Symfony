<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InventaireNouveauController extends AbstractController
{
    #[Route('/inventaire/nouveau', name: 'app_inventaire_nouveau')]
    public function index(): Response
    {
        return $this->render('inventaire_nouveau/index.html.twig', [
            'controller_name' => 'InventaireNouveauController',
        ]);
    }
}
