<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\DetailCommande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeZone; 
use Knp\Component\Pager\PaginatorInterface;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
#[Route('/commande')]
class CommandeController extends AbstractController
{
    private $knpSnappyPdf;

    public function __construct( Pdf $knpSnappyPdf)
    {
        
        $this->knpSnappyPdf = $knpSnappyPdf;
    }
    
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(Request $request, 
            CommandeRepository $produitRepository, 
            PaginatorInterface $paginator): Response
        {

            // filtrage ------------------------------------------------------------------------------------------------
            $sort = $request->query->get('sort'); // Get sort parameter
            $order = $request->query->get('order'); // Get order parameter
            
            // recherche ------------------------------------------------------------------------------------------------
            $searchCriteria = []; // Initialize empty search criteria
            $orderBy = null; // Initialize empty order by

            // Get search parameters from request query (if any)
            $allowedAttributes = ['numfacture', 'datecommande']; // Define allowed attributes
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
                5 // Nombre d'éléments par page
            );
            
            $pagination = $paginator->paginate(
            $produitRepository->findAll(),
            $request->query->getInt('page', 1),
            9 // Nombre d'éléments par page
            );

            return $this->render('commande/index.html.twig', [
                'pagination' => $pagination,
                'searchCriteria' => $searchCriteria, // Pass search criteria to Twig template for display
                'allowedAttributes' => $allowedAttributes,
                'order' => $order,
                'sort' => $sort,
            ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();

        $now = new \DateTime('now', new DateTimeZone('Europe/Moscow')); // Create DateTime with +3 timezone

        $commande->setDatecommande($now);

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Auto-incrémentation du numéro de facture à partir de 100
                $lastCommande = $entityManager->getRepository(Commande::class)->findOneBy([], ['numfacture' => 'DESC']);
                $lastNumFacture = $lastCommande ? intval($lastCommande->getNumfacture()) : 99;
                $newNumFacture = $lastNumFacture + 1;
                $commande->setNumfacture(strval($newNumFacture));
                $entityManager->persist($commande);
                $selectedProducts = [];

                for ($i = 0; $i < 20; $i++) {
                    $produitId = $request->request->get("produit-$i");
                    $quantite = $request->request->get("quantite-$i");
                    $prixUnitaire = $request->request->get("prixunitaire-$i");

                    if ($produitId && $prixUnitaire && $quantite) {
                        if (in_array($produitId, $selectedProducts)) {
                            $this->addFlash('alert_double', 'Le produit est sélectionné plusieurs fois.');
                            
                            return $this->render('commande/new.html.twig', [
                                'commande' => $commande,
                                'form' => $form,
                            ]);
                        }
            
                        $selectedProducts[] = $produitId;

                        $produit = $entityManager->getRepository(Produit::class)->find($produitId);
                        if( $produit->quantite() < $quantite){
                            //dd("quantite insuffisant");
                            $this->addFlash('alert_new_commande', 'Quantite insuffisant');

                            return $this->render('commande/new.html.twig', [
                                'commande' => $commande,
                                'form' => $form,
                            ]);
                        } 

                        if ($produit) {
                            $detail = new DetailCommande();
                            $detail->setIdCommande($commande);
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

                return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('commande/new.html.twig', [
                'commande' => $commande,
                'form' => $form,
            ]);
        }


    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_commandes', methods: ['GET'])]
    public function exportcommandes(commandeRepository $commandeRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Récupérez les commandes de la base de données
        $commandes = $commandeRepository->findAll();

        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'Numero de commande');
        $sheet->setCellValue('B1', 'Date de commande');
        $sheet->setCellValue('C1', 'Ville');
        $sheet->setCellValue('D1', 'Client');
        $sheet->setCellValue('E1', 'Etat de la Commmande');
    
        // Écrivez les données dans la feuille de calcul
        $row = 1; // Start data from row 2 (after headers)

        // Écrivez les données dans la feuille de calcul
        foreach ($commandes as $commande) {
            $sheet->setCellValue('A' . ($row+1), $commande->getId());
            $sheet->setCellValue('B' . ($row+1), $commande->getdatecommande());
            $sheet->setCellValue('C' . ($row+1), $commande->getIdVille()->getnom());
            $sheet->setCellValue('D' . ($row+1), $commande->getIdClient()->getnom());
            // Appliquez la méthode setEtatCommande() avec la valeur booléenne
    $commande->setEtatCommande($commande->isetatCommande());
    
    // Écrire "Effectuer" ou "Non Effectuer" dans la feuille de calcul
    $sheet->setCellValue('E' . ($row+1), $commande->isEtatCommande() ? "Effectuer" : "Non Effectuer");
            $row++;

        }

        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'commandes');
        $writer->save($temp_file);

        $this->addFlash('export_xls', 'Exportation en excel términée');
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'commandes.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/generatepdf/{id}', name: 'app_commande_pdf', methods: ['GET'])]
    public function generatePdf(Commande $commande, Request $request): Response
    {
        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();
        
        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        
        // Votre code existant pour le rendu HTML
        $html = $this->renderView('commande/facture.html.twig', [
            'commande' => $commande,
            'currentDateTime' => $formattedDateTime // Passer la date formatée à la vue Twig
        ]);

        $filename = sprintf('AeroSTOCK-commande-%s.pdf', $commande->getId());
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/commande/' . $filename;

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

    #[Route('/listpdfcommande', name: 'app_commande_list', methods: ['GET'])]
    public function listPdf(commandeRepository $commandeRepository, Request $request): Response
    {
        $commande = $commandeRepository->findAll();

        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();

        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

        $html = $this->renderView('commande/pdf.html.twig', [
            'commandes' => $commande,
            'currentDateTime' => $formattedDateTime
        ]);

        $filename = sprintf('AeroSTOCK-commande.pdf');
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/commande/' . $filename;

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
