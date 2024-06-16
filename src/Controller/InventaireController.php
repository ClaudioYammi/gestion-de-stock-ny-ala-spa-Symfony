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
use Knp\Component\Pager\PaginatorInterface;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Route('/inventaire')]
class InventaireController extends AbstractController
{
    private $knpSnappyPdf;
    

    public function __construct( Pdf $knpSnappyPdf)
    {
        
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

    #[Route('/', name: 'app_inventaire_index', methods: ['GET'])]
    public function index(Request $request, 
                        InventaireRepository $inventaireRepository,
                        PaginatorInterface $paginator
    ): Response
    {
        // filtrage ------------------------------------------------------------------------------------------------
        $sort = $request->query->get('sort'); // Get sort parameter
        $order = $request->query->get('order'); // Get order parameter
        
        // recherche ------------------------------------------------------------------------------------------------
        $searchCriteria = []; // Initialize empty search criteria
        $orderBy = null; // Initialize empty order by

        // Get search parameters from request query (if any)
        $allowedAttributes = [ 'update_at', 'reference', 'stockinventaire', 'stockutiliser' ]; // Define allowed attributes
        foreach ($allowedAttributes as $attribute) {
            if ($request->query->has($attribute)) {
                $searchCriteria[$attribute] = $request->query->get($attribute);
            }
        }

        // Get order by parameter from request query (if any)
        if ($request->query->has('sort')) {
            $orderBy = [$request->query->get('sort') => $request->query->get('direction', 'ASC')];
        }

        // Recherche entre deux dates
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $inventaires = $inventaireRepository->findBetweenDates($startDate, $endDate);
        } else {
            $inventaires = $inventaireRepository->findByCriteriaAllowed($searchCriteria, $orderBy);
        }

        $pagination = $paginator->paginate(
            $inventaires,
            $request->query->getInt('page', 1),
            8 // Nombre d'éléments par page
        );

        return $this->render('inventaire/index.html.twig', [
            'pagination' => $pagination,
            'searchCriteria' => $searchCriteria, // Pass search criteria to Twig template for display
            'allowedAttributes' => $allowedAttributes,
            'order' => $order,
            'sort' => $sort,
            'startDate' => $startDate ? $startDate->format('Y-m-d') : '',
            'endDate' => $endDate ? $endDate->format('Y-m-d') : '',
        ]);
    }

    #[Route('/new', name: 'app_inventaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération de tous les produits pour remplir le champ "Référence"
        $produits = $entityManager->getRepository(Produit::class)->findAll();

        // Traitement de la requête POST pour créer un nouvel inventaire
        if ($request->isMethod('POST')) {
            // Récupération de la date actuelle avec le fuseau horaire +3
            $updateAt = new \DateTimeImmutable('now', new \DateTimeZone('+03:00'));
            $selectedProducts = [];

            // Récupération des données des lignes du formulaire
            $notes = $request->get('note');
            $stocksInventaire = $request->get('stockinventaire');
            $stocksUtiliser = $request->get('stockutiliser');
            $references = $request->get('reference');

            // Vérification des duplications de produits
            $selectedReferences = [];
            for ($i = 0; $i < count($references); $i++) {
                if (in_array($references[$i], $selectedReferences)) {
                    $this->addFlash('alert_new_achat', 'Le produit avec la référence ' . $references[$i] . ' est sélectionné plusieurs fois.');
                    return $this->render('inventaire/nouveau.html.twig', [
                        'produits' => $produits,
                        'updateAt' => $updateAt->format('Y-m-d\TH:i:s'),
                    ]);
                }

                // Vérification que les stocks ne sont pas négatifs
                if ($stocksInventaire[$i] < 0 || $stocksUtiliser[$i] < 0) {
                    $this->addFlash('alert_new_achat', 'Les stocks ne peuvent pas être négatifs.');
                    return $this->render('inventaire/nouveau.html.twig', [
                        'produits' => $produits,
                        'updateAt' => $updateAt->format('Y-m-d\TH:i:s'),
                    ]);
                }
                $selectedReferences[] = $references[$i];
            }

            // Itération sur chaque ligne du formulaire
            for ($i = 0; $i < count($notes); $i++) {
                $inventaire = new Inventaire();
                
                $inventaire
                    ->setUpdateAt($updateAt)
                    ->setNote($notes[$i])
                    ->setStockinventaire($stocksInventaire[$i])
                    ->setStockutiliser($stocksUtiliser[$i]);

                // Récupération du produit de référence à partir de l'ID envoyé dans le formulaire
                $reference = $entityManager->getRepository(Produit::class)->find($references[$i]);
                $inventaire->setReference($reference);

                // Sauvegarde de l'inventaire dans la base de données
                $entityManager->persist($inventaire);
            }

            // Exécution de toutes les insertions en une seule transaction
            $entityManager->flush();
            $this->addFlash('success', 'Inventaire a été effectuer avec succès.');
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
    #[ParamDecryptor(['id'])]
    public function show(Inventaire $inventaire): Response
    {
        return $this->render('inventaire/show.html.twig', [
            'inventaire' => $inventaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_inventaire_edit', methods: ['GET', 'POST'])]
    #[ParamDecryptor(['id'])]
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
    #[ParamDecryptor(['id'])]
    public function delete(Request $request, Inventaire $inventaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$inventaire->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($inventaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_inventaire_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_inventaires', methods: ['GET'])]
    public function exportInventaires(InventaireRepository $inventaireRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Récupérez les inventaires de la base de données
        $inventaires = $inventaireRepository->findAll();
    
        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'Date inventaire');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Stock inventaire');
        $sheet->setCellValue('D1', 'Stock utiliser');
        $sheet->setCellValue('E1', 'Ecart');
    
        // Écrivez les données dans la feuille de calcul
        $row = 2; // Start data from row 2 (after headers)
        foreach ($inventaires as $inventaire) {
            $sheet->setCellValue('A' . $row, $inventaire->getUpdateAt());
            $sheet->setCellValue('B' . $row, $inventaire->getreference()->getdesignation());
            $sheet->setCellValue('C' . $row, $inventaire->getstockinventaire());
            $sheet->setCellValue('D' . $row, $inventaire->getstockutiliser());
            $sheet->setCellValue('E' . $row, $inventaire->calculerEcart());
            $row++;
        }
    
        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'inventaires');
        $writer->save($temp_file);
    
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'inventaires.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/listpdf', name: 'app_inventaire_list', methods: ['GET'])]
    public function listPdf(InventaireRepository $inventaireRepository,Request $request): Response
    {
        $inventaire = $inventaireRepository->findAll();

        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();

        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

        $html = $this->renderView('inventaire/pdf.html.twig', [
            'inventaires' => $inventaire,
            'currentDateTime' => $formattedDateTime // Passer la date formatée à la vue Twig
            
        ]);

        $filename = sprintf('AeroSTOCK-inventaire.pdf');
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/inventaire/' . $filename;

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
