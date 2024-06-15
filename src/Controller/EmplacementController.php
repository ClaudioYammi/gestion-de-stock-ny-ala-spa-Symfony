<?php

namespace App\Controller;

use App\Entity\Emplacement;
use App\Form\EmplacementType;
use App\Repository\EmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/emplacement')]
class EmplacementController extends AbstractController
{
    #[Route('/', name: 'app_emplacement_index', methods: ['GET', 'POST'])]
    public function index(Request $request, 
                EmplacementRepository $emplacementRepository, 
                PaginatorInterface $paginator
                , EntityManagerInterface $entityManager): Response
            {
            #modal---------------------------------------------------------------------
            $emplacement = new Emplacement();
            $form = $this->createForm(EmplacementType::class, $emplacement);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($emplacement);
                $entityManager->flush();

                return $this->redirectToRoute('app_emplacement_index', [], Response::HTTP_SEE_OTHER);
            }

            // filtrage ------------------------------------------------------------------------------------------------
            $sort = $request->query->get('sort'); // Get sort parameter
            $order = $request->query->get('order'); // Get order parameter

            // recherche ------------------------------------------------------------------------------------------------
            $searchCriteria = []; // Initialize empty search criteria
            $orderBy = null; // Initialize empty order by

            // Get search parameters from request query (if any)
            $allowedAttributes = ['nom']; // Define allowed attributes
            foreach ($allowedAttributes as $attribute) {
                if ($request->query->has($attribute)) {
                    $searchCriteria[$attribute] = $request->query->get($attribute);
                }
            }
            
            // Get order by parameter from request query (if any)
            if ($request->query->has('sort')) {
                $orderBy = [$request->query->get('sort') => $request->query->get('direction', 'ASC')];
            }

            #pagination ----------------------------------------------------------------
            $pagination = $paginator->paginate(
                $emplacementRepository->findByCriteriaAllowed($searchCriteria, $orderBy),
                $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
            );
            
            return $this->render('emplacement/index.html.twig', [
            'pagination' => $pagination,
            'searchCriteria' => $searchCriteria, // Pass search criteria to Twig template for display
            'allowedAttributes' => $allowedAttributes,
            'form' => $form,
            'order' => $order,
            'sort' => $sort,

            ]);
    }

    #[Route('/new', name: 'app_emplacement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $emplacement = new Emplacement();
        $form = $this->createForm(EmplacementType::class, $emplacement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($emplacement);
            $entityManager->flush();

            $this->addFlash('success', 'Emplacement a été effectuer avec succès.');
            return $this->redirectToRoute('app_emplacement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emplacement/new.html.twig', [
            'emplacement' => $emplacement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_emplacement_show', methods: ['GET'])]
    public function show(Emplacement $emplacement): Response
    {
        return $this->render('emplacement/show.html.twig', [
            'emplacement' => $emplacement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_emplacement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Emplacement $emplacement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmplacementType::class, $emplacement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('edit', 'Emplacement a été modifier avec succès.');
            return $this->redirectToRoute('app_emplacement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emplacement/edit.html.twig', [
            'emplacement' => $emplacement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_emplacement_delete', methods: ['POST'])]
    public function delete(Request $request, Emplacement $emplacement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$emplacement->getId(), $request->request->get('_token'))) {
            // Vérifiez si le categorie est lié à d'autres données
            if (count($emplacement->getProduits()) > 0) {
                $this->addFlash('error', 'Impossible de supprimer cet emplacement car il est lié à d\'autres données dans la table produit.');
            } else {
                try {
                    $entityManager->remove($emplacement);
                    $entityManager->flush();
                    $this->addFlash('delete', 'L\'emplacement a été supprimé avec succès.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du emplacement.');
                }
            }
        }

        return $this->redirectToRoute('app_emplacement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_emplacements', methods: ['GET'])]
    public function exportemplacements(EmplacementRepository $emplacementRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Récupérez les emplacements de la base de données
        $emplacements = $emplacementRepository->findAll();
    
        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'No ');
        $sheet->setCellValue('B1', 'Nom emplacement');
    
        // Écrivez les données dans la feuille de calcul
        $row = 2; // Start data from row 2 (after headers)
        foreach ($emplacements as $emplacement) {
            $sheet->setCellValue('A' . $row, $emplacement->getId());
            $sheet->setCellValue('B' . $row, $emplacement->getNom());
            $row++;

        }
    
        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'achats');
        $writer->save($temp_file);
    
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'achats.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
