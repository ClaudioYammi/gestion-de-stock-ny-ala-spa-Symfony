<?php

namespace App\Controller;

use App\Entity\DetailCommande;
use App\Form\DetailCommandeType;
use App\Repository\DetailCommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeZone; 
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;

#[Route('/detail/commande')]
class DetailCommandeController extends AbstractController
{
    #[Route('/', name: 'app_detail_commande_index', methods: ['GET'])]
    public function index(DetailCommandeRepository $detailCommandeRepository): Response
    {
        return $this->render('detail_commande/index.html.twig', [
            'detail_commandes' => $detailCommandeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_detail_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $detailCommande = new DetailCommande();

        $form = $this->createForm(DetailCommandeType::class, $detailCommande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($detailCommande);
            $entityManager->flush();

            return $this->redirectToRoute('app_detail_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('detail_commande/new.html.twig', [
            'detail_commande' => $detailCommande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detail_commande_show', methods: ['GET'])]
    public function show(DetailCommande $detailCommande): Response
    {
        return $this->render('detail_commande/show.html.twig', [
            'detail_commande' => $detailCommande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_detail_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DetailCommande $detailCommande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DetailCommandeType::class, $detailCommande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_detail_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('detail_commande/edit.html.twig', [
            'detail_commande' => $detailCommande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detail_commande_delete', methods: ['POST'])]
    public function delete(Request $request, DetailCommande $detailCommande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$detailCommande->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($detailCommande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_detail_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
