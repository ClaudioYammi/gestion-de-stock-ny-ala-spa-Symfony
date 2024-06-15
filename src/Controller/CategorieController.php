<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'app_categorie_index', methods: ['GET', 'POST'])]
    public function index(Request $request, 
                categorieRepository $categorieRepository, 
                PaginatorInterface $paginator,
                EntityManagerInterface $entityManager
                ): Response
            {
                
            $ville = new Categorie();
            $form = $this->createForm(CategorieType::class, $ville);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($ville);
                $entityManager->flush();

                return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
            }

            // filtrage ------------------------------------------------------------------------------------------------
            $sort = $request->query->get('sort'); // Get sort parameter
            $order = $request->query->get('order'); // Get order parameter
            
            // recherche ------------------------------------------------------------------------------------------------
            $searchCriteria = []; // Initialize empty search criteria
            $orderBy = null; // Initialize empty order by

            // Get search parameters from request query (if any)
            $allowedAttributes = ['nom', 'description']; // Define allowed attributes
            foreach ($allowedAttributes as $attribute) {
                if ($request->query->has($attribute)) {
                    $searchCriteria[$attribute] = $request->query->get($attribute);
                }
            }

            // Get order by parameter from request query (if any)
            if ($request->query->has('sort')) {
                $orderBy = [$request->query->get('sort') => $request->query->get('direction', 'ASC')];
            }

            $pagination = $paginator->paginate(
                $categorieRepository->findByCriteriaAllowed($searchCriteria, $orderBy),
                $request->query->getInt('page', 1),
                5 // Nombre d'éléments par page
            );

            return $this->render('categorie/index.html.twig', [
            'pagination' => $pagination,
            'searchCriteria' => $searchCriteria, // Pass search criteria to Twig template for display
            'allowedAttributes' => $allowedAttributes,
            'order' => $order,
            'sort' => $sort,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'Categorie a été effectuer avec succès.');
            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('edit', 'Categorie a été modifier avec succès.');
            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            // Vérifiez si le categorie est lié à d'autres données
            if (count($categorie->getProduits()) > 0) {
                $this->addFlash('error', 'Impossible de supprimer ce categorie car il est lié à d\'autres données dans la table produit.');
            } else {
                try {
                    $entityManager->remove($categorie);
                    $entityManager->flush();
                    $this->addFlash('delete', 'Le categorie a été supprimé avec succès.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du categorie.');
                }
            }
        }

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_categories', methods: ['GET'])]
    public function exportcategories(CategorieRepository $categorieRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Récupérez les categories de la base de données
        $categories = $categorieRepository->findAll();
    
        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Description');
    
        // Écrivez les données dans la feuille de calcul
        $row = 2; // Start data from row 2 (after headers)
        foreach ($categories as $categorie) {
            $sheet->setCellValue('A' . $row, $categorie->getId());
            $sheet->setCellValue('A' . $row, $categorie->getNom());
            $sheet->setCellValue('A' . $row, $categorie->getDescription());
            $row++;
        }
    
        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'categories');
        $writer->save($temp_file);
    
        $this->addFlash('export_xls', 'Exportation en excel términée');
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'categories.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
