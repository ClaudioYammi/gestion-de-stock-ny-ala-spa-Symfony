<?php

namespace App\Controller;

use App\Entity\Inventaire;
use App\Entity\Produit;
use App\Form\InventaireType;
use App\Repository\InventaireRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeZone;

#[Route('/inventaire')]
class InventaireController extends AbstractController
{
    #[Route('/', name: 'app_inventaire_index', methods: ['GET'])]
    public function index(InventaireRepository $inventaireRepository
    ): Response
    {
        return $this->render('inventaire/index.html.twig', [
            'inventaires' => $inventaireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_inventaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
    // Récupération de tous les produits pour remplir le champ "Référence"
    $produits = $entityManager->getRepository(Produit::class)->findAll();
        // Traitement de la requête POST pour créer un nouvel inventaire
        if ($request->isMethod('POST')) {
            $inventaire = new Inventaire();

            // Récupération de la date actuelle avec le fuseau horaire +3
            $updateAt = new \DateTimeImmutable('now', new \DateTimeZone('+03:00'));
            
            $inventaire
            ->setUpdateAt($updateAt)
            ->setNote($request->request->get('note'))
            ->setStockinventaire($request->request->get('stockinventaire'))
            ->setStockutiliser($request->request->get('stockutiliser'));

            // Récupération du produit de référence à partir de l'ID envoyé dans le formulaire
            $reference = $entityManager->getRepository(Produit::class)->find($request->request->get('reference'));
                $inventaire->setReference($reference);
                
                // Sauvegarde de l'inventaire dans la base de données
            $entityManager->persist($inventaire);
            $entityManager->flush();

            // Redirection vers la liste des inventaires
            return $this->redirectToRoute('app_inventaire_index');
        }
        
        $updateAt = new \DateTimeImmutable('now', new \DateTimeZone('+03:00'));
        // Rendu du template avec la liste des produits et la date de mise à jour
        return $this->render('inventaire/nouveau.html.twig', [
            'produits' => $produits,
            'updateAt' => $updateAt->format('Y-m-d\TH:i:s'),
        ]);
    }

    #[Route('/{id}', name: 'app_inventaire_show', methods: ['GET'])]
    public function show(Inventaire $inventaire): Response
    {
        return $this->render('inventaire/show.html.twig', [
            'inventaire' => $inventaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_inventaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Inventaire $inventaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InventaireType::class, $inventaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_inventaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inventaire/edit.html.twig', [
            'inventaire' => $inventaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inventaire_delete', methods: ['POST'])]
    public function delete(Request $request, Inventaire $inventaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$inventaire->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($inventaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_inventaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
