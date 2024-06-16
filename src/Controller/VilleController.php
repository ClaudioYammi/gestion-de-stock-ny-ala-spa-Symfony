<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;

#[Route('/ville')]
class VilleController extends AbstractController
{
    #[Route('/', name: 'app_ville_index', methods: ['GET', 'POST'])]
    public function index(VilleRepository $villeRepository, Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', 'Ville a été créée avec succès.');
            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

        $sort = $request->query->get('sort'); // Get sort parameter
        $order = $request->query->get('order'); // Get order parameter

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

        $pagination = $paginator->paginate(
            $villeRepository->findByCriteriaAllowed($searchCriteria, $orderBy),
            $request->query->getInt('page', 1),
            10 // Number of elements per page
        );
        
        return $this->render('ville/index.html.twig', [
            'pagination' => $pagination,
            'searchCriteria' => $searchCriteria, // Pass search criteria to Twig template for display
            'allowedAttributes' => $allowedAttributes, // Pass allowed attributes to Twig template for form generation
            'order' => $order,
            'sort' => $sort,
            'form' => $form,

        ]);
    }
    
    #[Route('/new', name: 'app_ville_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', 'Ville a été créée avec succès.');
            
            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

            return $this->render('_layouts/_notification.html.twig');
            return $this->render('ville/new.html.twig', [
                'ville' => $ville,
                'form' => $form->createView(),
            ]);
    }

    #[Route('/{id}', name: 'app_ville_show', methods: ['GET'])]
    #[ParamDecryptor(['id'])]
    public function show(Ville $ville): Response
    {
        return $this->render('ville/show.html.twig', [
            'ville' => $ville,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ville_edit', methods: ['GET', 'POST'])]
    #[ParamDecryptor(['id'])]
    public function edit(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('edit', 'Ville a été modifier avec succès.');
            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ville/edit.html.twig', [
            'ville' => $ville,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ville_delete', methods: ['POST'])]
    #[ParamDecryptor(['id'])]
    public function delete(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ville->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($ville);
            $entityManager->flush();

            $this->addFlash('delete', 'Ville a été supprimer avec succès.');
        }

        return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_villes', methods: ['GET'])]
    public function exportvilles(VilleRepository $villeRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Récupérez les villes de la base de données
        $villes = $villeRepository->findAll();
    
        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nom');
    
        // Écrivez les données dans la feuille de calcul
        $row = 2; // Start data from row 2 (after headers)
        foreach ($villes as $ville) {
            $sheet->setCellValue('A' . $row, $ville->getId());
            $sheet->setCellValue('A' . $row, $ville->getNom());
            $row++;
        }
    
        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'villes');
        $writer->save($temp_file);
    
        $this->addFlash('export_xls', 'Exportation en excel términée');
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'villes.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
