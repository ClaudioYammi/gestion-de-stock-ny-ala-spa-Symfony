<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Entity\Produit;
use App\Entity\DetailAchat;
use App\Form\AchatType;
use App\Repository\AchatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeZone;
use Knp\Component\Pager\PaginatorInterface;

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Route('/achat')]
class AchatController extends AbstractController
{
    private $knpSnappyPdf;
    

    public function __construct( Pdf $knpSnappyPdf)
    {
        
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

    #[Route('/', name: 'app_achat_index', methods: ['GET'])]
    public function index(Request $request, 
                        AchatRepository $produitRepository, 
                        PaginatorInterface $paginator): Response
    {
        
        // filtrage ------------------------------------------------------------------------------------------------
        $sort = $request->query->get('sort'); // Get sort parameter
        $order = $request->query->get('order'); // Get order parameter
        
        // recherche ------------------------------------------------------------------------------------------------
        $searchCriteria = []; // Initialize empty search criteria
        $orderBy = null; // Initialize empty order by

        // Get search parameters from request query (if any)
        $allowedAttributes = ['numfacture', 'dateachat']; // Define allowed attributes
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
            8 // Nombre d'éléments par page
        );

        return $this->render('achat/index.html.twig', [
            'pagination' => $pagination,
            'searchCriteria' => $searchCriteria, // Pass search criteria to Twig template for display
            'allowedAttributes' => $allowedAttributes,
            'order' => $order,
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_achat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $achat = new Achat();

        $now = new \DateTime('now', new DateTimeZone('Europe/Moscow')); // Create DateTime with +3 timezone

        $achat->setDateachat($now);

        $form = $this->createForm(AchatType::class, $achat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Auto-incrémentation du numéro de facture à partir de 100
            $lastAchat = $entityManager->getRepository(Achat::class)->findOneBy([], ['numfacture' => 'DESC']);
            $lastNumFacture = $lastAchat ? intval($lastAchat->getNumfacture()) : 99;
            $newNumFacture = $lastNumFacture + 1;
            $achat->setNumfacture(strval($newNumFacture));
            $entityManager->persist($achat);
            
            $details = [];
            $selectedProducts = [];

            for ($i = 0; $i < 20; $i++) {
                $produitId = $request->request->get("produit-$i");
                $quantite = $request->request->get("quantite-$i");
                $prixUnitaire = $request->request->get("prixunitaire-$i");
    
                if ($produitId && $prixUnitaire && $quantite) {
                    if (in_array($produitId, $selectedProducts)) {
                        $this->addFlash('alert_new_achat', 'Le produit est sélectionné plusieurs fois.');
                        
                        return $this->render('achat/new.html.twig', [
                            'achat' => $achat,
                            'form' => $form,
                        ]);
                    }
        
                    $selectedProducts[] = $produitId;
                    $produit = $entityManager->getRepository(Produit::class)->find($produitId);


                    if ($produit) {
                        $detail = new DetailAchat();
                        $detail->setidAchat($achat);
                        $detail->setReference($produit);
                        $detail->setPrixUnitaire($prixUnitaire);
                        $detail->setQuantite($quantite);
                        $details[] = $detail;

                    }
                }
            }
    
            foreach ($details as $detail) {
                $entityManager->persist($detail);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Achat a été effectuer avec succès.');

            return $this->redirectToRoute('app_achat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('achat/new.html.twig', [
            'achat' => $achat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_achat_show', methods: ['GET'])]
    public function show(Achat $achat): Response
    {
        return $this->render('achat/show.html.twig', [
            'achat' => $achat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_achat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Achat $achat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AchatType::class, $achat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('edit', 'Achat a été modifier avec succès.');
            return $this->redirectToRoute('app_achat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('achat/edit.html.twig', [
            'achat' => $achat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_achat_delete', methods: ['POST'])]
    public function delete(Request $request, Achat $achat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$achat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($achat);
            $entityManager->flush();

            $this->addFlash('delete', 'Achat a été supprimer avec succès.');
        }

        return $this->redirectToRoute('app_achat_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_achats', methods: ['GET'])]
    public function exportachats(AchatRepository $achatRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Récupérez les achats de la base de données
        $achats = $achatRepository->findAll();
    
        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'Num facture');
        $sheet->setCellValue('B1', 'Fournisseur');
        $sheet->setCellValue('C1', 'Date achat');
        $sheet->setCellValue('D1', 'Total achat');
    
        // Écrivez les données dans la feuille de calcul
        $row = 2; // Start data from row 2 (after headers)
        foreach ($achats as $achat) {
            $sheet->setCellValue('A' . $row, $achat->getNumfacture());
            $sheet->setCellValue('B' . $row, $achat->getIdFournisseur()->getNom());
            $sheet->setCellValue('B' . $row, $achat->getDateAchat());
            $sheet->setCellValue('C' . $row, $achat->total() . ' Ariary');
            $row++;
        }
    
        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'achats');
        $writer->save($temp_file);
    
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'achats.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/generatepdf/{id}', name: 'app_achat_pdf', methods: ['GET'])]
    public function generatePdf(Achat $achat,  Request $request): Response
    {
        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();
        
        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        
        // Votre code existant pour le rendu HTML
        $html = $this->renderView('achat/facture.html.twig', [
            'achat' => $achat,
            'currentDateTime' => $formattedDateTime // Passer la date formatée à la vue Twig
        ]);

        $filename = sprintf('AeroSTOCK-achat-%s.pdf', $achat->getId());
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/achat/' . $filename;

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

    #[Route('/listpdf', name: 'app_achat_list', methods: ['GET'])]
    public function listPdf(AchatRepository $achatRepository,Request $request): Response
    {
        $achat = $achatRepository->findAll();

        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();

        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

        $html = $this->renderView('achat/pdf.html.twig', [
            'achats' => $achat,
            'currentDateTime' => $formattedDateTime // Passer la date formatée à la vue Twig
            
        ]);

        $filename = sprintf('AeroSTOCK-achat.pdf');
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/achat/' . $filename;

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
