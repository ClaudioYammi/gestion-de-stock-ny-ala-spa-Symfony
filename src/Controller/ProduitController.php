<?php

namespace App\Controller;
use App\Repository\AchatRepository;
use App\Repository\VenteRepository;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use App\Entity\Inventaire;
use App\Form\InventaireType;
use App\Repository\InventaireRepository;
use DateTimeZone;

use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
#[Route('/produit')]
class ProduitController extends AbstractController
{
    private $inventaireRepository;
    private $entityManager;
    private $knpSnappyPdf;

    public function __construct(InventaireRepository $inventaireRepository, Pdf $knpSnappyPdf, EntityManagerInterface $entityManager)
    {
        $this->inventaireRepository = $inventaireRepository;
        $this->entityManager = $entityManager;
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository, PaginatorInterface $paginator): Response
    {
        $sort = $request->query->get('sort'); // Get sort parameter
        $order = $request->query->get('order'); // Get order parameter

        $searchCriteria = []; // Initialize empty search criteria
        $orderBy = null; // Initialize empty order by

        

        // Get search parameters from request query (if any)
        $allowedAttributes = ['designation', 'emplacement', 'prixunitaire', 'prixunitairevente']; // Define allowed attributes
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
            $produitRepository->findByCriteriaAllowed($searchCriteria, $orderBy),
            $request->query->getInt('page', 1),
            5 // Number of elements per page
        );

        return $this->render('produit/index.html.twig', [
            'pagination' => $pagination,
            'searchCriteria' => $searchCriteria, // Pass search criteria to Twig template for display
            'allowedAttributes' => $allowedAttributes, // Pass allowed attributes to Twig template for form generation
            'order' => $order,
            'sort' => $sort,
        ]);
    }

    #[Route('/api', name: 'api_produit_index', methods: ['GET'])]
    public function apiIndex(ProduitRepository $produitRepository): JsonResponse
    {
        $produits = $produitRepository->findAll();

        $data = [];
        foreach ($produits as $produit) {
            $data[] = [
                'id' => $produit->getId(),
                'designation' => $produit->getDesignation(),
                'price' => $produit->getPrixunitaire(),
                'pricesell' => $produit->getPrixunitairevente(),
                // Ajoutez ici d'autres propriétés du produit que vous souhaitez inclure dans la réponse JSON
            ];
        }
        return new JsonResponse($data);
    }

    #[Route('/api/{id}', name: 'api_produit_edit', methods: ['PUT'])]
    public function apiEdit(Request $request, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = $request->attributes->get('id');
        $designation = $request->request->get('designation');
        $price = $request->request->get('price');

        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $produit->setDesignation($designation);
        $produit->setPrixunitaire($price);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Produit modifié avec succès.']);
    }
    
    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $produit->getImageFile();

            if ($imageFile && $imageFile->getSize() > 5 * 1024 * 1024) { // 5MB in bytes
                $this->addFlash('error_image', 'L\'image ne doit pas dépasser 5 MB.');
            } else {
                $entityManager->persist($produit);
                $entityManager->flush();

                $this->addFlash('success', 'Produit a été créé avec succès.');
                return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('edit', 'Produit a été modifier avec succès.');
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            // Vérifiez si le categorie est lié à d'autres données
            if (count($produit->getDetailAchats()) > 0) {
                $this->addFlash('error', 'Impossible de supprimer ce produit car il est lié à d\'autres données dans la table achat.');
            } elseif (count($produit->getDetailVentes()) > 0)  {
                $this->addFlash('error', 'Impossible de supprimer ce produit car il est lié à d\'autres données dans la table vente.');
            } 
            else {
                try {
                    $entityManager->remove($produit);
                    $entityManager->flush();
                    $this->addFlash('delete', 'Le produit a été supprimé avec succès.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du produit.');
                }
            }
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_produits', methods: ['GET'])]
    public function exportProduits(ProduitRepository $produitRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        

        // Récupérez les produits de la base de données
        $produits = $produitRepository->findAll();

        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Designation');
        $sheet->setCellValue('C1', 'Emplacement');
        $sheet->setCellValue('D1', 'Prix unitaire');
        $sheet->setCellValue('E1', 'Stock');
        $sheet->setCellValue('G1', 'Categorie');
    
        // Écrivez les données dans la feuille de calcul
        $row = 1; // Start data from row 2 (after headers)
        foreach ($produits as $produit) {
            $sheet->setCellValue('A' . ($row+1), $produit->getId());
            $sheet->setCellValue('B' . ($row+1), $produit->getDesignation());
            $sheet->setCellValue('C' . ($row+1), $produit->getEmplacement()->getNom());
            $sheet->setCellValue('D' . ($row+1), $produit->getPrixunitaire());
            $sheet->setCellValue('E' . ($row+1), $produit->getQuantiteAchat()-$produit->getQuantiteVente());
            $sheet->setCellValue('F' . ($row+1), $produit->getidCategorie());
            $row++;
            
        }

        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'produits');
        $writer->save($temp_file);

        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'produits.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/generate-pdf/{id}', name: 'app_produit_generate_pdf', methods: ['GET'])]
    public function generatePdf(Produit $produit, Request $request): Response
    {
        $html = $this->renderView('produit/facture.html.twig', [
            'produit' => $produit,
        ]);

        $filename = sprintf('facture-%s.pdf', $produit->getId());
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/' . $filename;

        // Vérifier si le fichier existe déjà
        if (file_exists($pdfPath)) {
            // Supprimer le fichier existant
            unlink($pdfPath);
        }

        $this->knpSnappyPdf->generateFromHtml($html, $pdfPath);

        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(
            $request->query->get('download') ? 'attachment' : 'inline',
            $filename
        );
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    #[Route('/listpdf', name: 'app_produit_list', methods: ['GET'])]
    public function listPdf(produitRepository $produitRepository,Request $request): Response
    {
        $produit = $produitRepository->findAll();

        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();

        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

        $html = $this->renderView('produit/pdf.html.twig', [
            'produits' => $produit,
            'currentDateTime' => $formattedDateTime // Passer la date formatée à la vue Twig   
        ]);

        $filename = sprintf('AeroSTOCK-produit.pdf');
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/produit/' . $filename;

        // Vérifier si le fichier existe déjà
        if (file_exists($pdfPath)) {
            // Supprimer le fichier existant
            unlink($pdfPath);
        }

        $this->knpSnappyPdf->generateFromHtml($html, $pdfPath);

        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(
            $request->query->get('download') ? 'attachment' : 'inline',
            $filename
        );
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }   
}
