<?php

namespace App\Controller;

use App\Entity\DetailVente;
use App\Form\DetailVenteType;
use App\Repository\DetailVenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;

#[Route('/detail/vente')]
class DetailVenteController extends AbstractController
{
    #[Route('/', name: 'app_detail_vente_index', methods: ['GET'])]
    public function index(DetailVenteRepository $detailVenteRepository): Response
    {
        return $this->render('detail_vente/index.html.twig', [
            'detail_ventes' => $detailVenteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_detail_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $detailVente = new DetailVente();
        $form = $this->createForm(DetailVenteType::class, $detailVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($detailVente);
            $entityManager->flush();

            return $this->redirectToRoute('app_detail_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('detail_vente/new.html.twig', [
            'detail_vente' => $detailVente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detail_vente_show', methods: ['GET'])]
    public function show(DetailVente $detailVente): Response
    {
        return $this->render('detail_vente/show.html.twig', [
            'detail_vente' => $detailVente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_detail_vente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DetailVente $detailVente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DetailVenteType::class, $detailVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_detail_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('detail_vente/edit.html.twig', [
            'detail_vente' => $detailVente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detail_vente_delete', methods: ['POST'])]
    public function delete(Request $request, DetailVente $detailVente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$detailVente->getId(), $request->request->get('_token'))) {
            $entityManager->remove($detailVente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_detail_vente_index', [], Response::HTTP_SEE_OTHER);
    }
}
