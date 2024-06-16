<?php

namespace App\Controller;

use App\Entity\Vente;
use App\Entity\DetailVente;
use App\Entity\Produit;
use App\Form\VenteType;
use App\Form\DetailVenteType;
use App\Repository\VenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Knp\Bundle\Snappy\SnappyBundle;
use DateTimeZone;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;

#[Route('/vente')]
class VenteController extends AbstractController
{
    private $knpSnappyPdf;

    public function __construct( Pdf $knpSnappyPdf)
    {
        
        $this->knpSnappyPdf = $knpSnappyPdf;
    }
    #[Route('/', name: 'app_vente_index', methods: ['GET'])]
    public function index(VenteRepository $venteRepository): Response
    {
        return $this->render('vente/index.html.twig', [
            'ventes' => $venteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        
        $vente = new Vente();
        $now = new \DateTime('now', new DateTimeZone('Europe/Moscow')); // Create DateTime with +3 timezone

        $vente->setDatevente($now);
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Auto-incrémentation du numéro de facture à partir de 100
            $lastVente = $entityManager->getRepository(Vente::class)->findOneBy([], ['numfacture' => 'DESC']);
            $lastNumFacture = $lastVente ? intval($lastVente->getNumfacture()) : 99;
            $newNumFacture = $lastNumFacture + 1;
            $vente->setNumfacture(strval($newNumFacture));
            $entityManager->persist($vente);
            $details = [];
            $selectedProducts = [];

            for ($i = 0; $i < 20; $i++) {
                $produitId = $request->request->get("produit-$i");
                $quantite = $request->request->get("quantite-$i");
                $prixUnitairevente = $request->request->get("prixunitairevente-$i");

                if ($produitId && $prixUnitairevente && $quantite) {
                    if (in_array($produitId, $selectedProducts)) {
                        $this->addFlash('alert_double', 'Le produit est sélectionné plusieurs fois.');
                        
                        return $this->render('vente/new.html.twig', [
                            'vente' => $vente,
                            'form' => $form,
                        ]);
                    }
        
                    $selectedProducts[] = $produitId;
                    $produit = $entityManager->getRepository(Produit::class)->find($produitId);
                    
                    
                    if( $produit->quantite() < $quantite){
                        //dd("quantite insuffisant");
                        $this->addFlash('alert_low_vente', 'Quantite insuffisant');

                        return $this->render('vente/new.html.twig', [
                            'vente' => $vente,
                            'form' => $form,
                        ]);

                    } 

                    if ($produit) {
                        $detail = new DetailVente();
                        $detail->setidVente($vente);
                        $detail->setReference($produit);
                        $detail->setPrixunitairevente($prixUnitairevente);
                        $detail->setQuantite($quantite);
                        $details[] = $detail;

                        // Soustraire la quantité vendue du stock du produit
                    }
                }
            }

            foreach ($details as $detail) {
                $entityManager->persist($detail);
            }

            $this->addFlash('success', 'la Vente a été effectuer avec succès.');
            $entityManager->flush();

            return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vente/new.html.twig', [
            'vente' => $vente,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_vente_show', methods: ['GET'])]
    #[ParamDecryptor(['id'])]
    public function show(Vente $vente): Response
    {
        return $this->render('vente/show.html.twig', [
            'vente' => $vente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vente_edit', methods: ['GET', 'POST'])]
    #[ParamDecryptor(['id'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // Récupération de la vente et de ses détails
        $vente = $entityManager->getRepository(Vente::class)->find($id);

        // Vérification si la vente existe
        if (!$vente) {
            throw $this->createNotFoundException('Vente non trouvée avec l\'ID ' . $id);
        }

        // Récupération des détails de vente
        $details = $entityManager->getRepository(DetailVente::class)->findBy(['idVente' => $vente->getId()]);

        // Création du formulaire
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        // Traitement de la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour des détails de vente existants
            foreach ($details as $detail) {
                // Récupération des données soumises pour le détail
                $submittedProduit = $request->request->get("produit-" . $detail->getId());
                $submittedQuantite = $request->request->get("quantite-" . $detail->getId());
                $submittedPrixUnitaire = $request->request->get("prixunitaire-" . $detail->getId());

                // Vérification si des données ont été modifiées pour le détail
                if ($submittedProduit !== $detail->getReference()->getId() ||
                    (isset($submittedQuantite) && (int)$submittedQuantite !== $detail->getQuantite()) ||
                    (isset($submittedPrixUnitaire) && (float)$submittedPrixUnitaire !== $detail->getPrixunitaire())) {
                    // Mise à jour des propriétés du détail
                    if ($submittedProduit) {
                        $produit = $entityManager->getRepository(Produit::class)->find($submittedProduit);
                        $detail->setReference($produit); // Use the getReference() method
                    }

                    if (isset($submittedQuantite)) {
                        $detail->setQuantite((int)$submittedQuantite);
                    }
                    if (isset($submittedPrixUnitaire)) {
                        $detail->setPrixunitaire((float)$submittedPrixUnitaire);
                    }
                }
            }

            // Création de nouveaux détails de vente
            for ($i = count($details); $i < $request->request->get('nbDetails'); $i++) {
                $produitId = $request->request->get("produit-$i");
                $quantite = $request->request->get("quantite-$i");
                $prixUnitaire = $request->request->get("prixunitaire-$i");

                if ($produitId !== null && $quantite !== null && $prixUnitaire !== null) {
                    $produit = $entityManager->getRepository(Produit::class)->find($produitId);
                    $detail = new DetailVente();
                    $detail->setIdVente($vente);
                    $detail->setReference($produit);
                    $detail->setPrixunitairevente($prixUnitaire);
                    $detail->setQuantite($quantite);
                    $details[] = $detail;
                }
            }

            // Persistance et flush des entités
            foreach ($details as $detail) {
                $entityManager->persist($detail);
            }
            $this->addFlash('edit', 'vente a été modifier avec succès.');

            $entityManager->flush();

            // Redirection après modification réussie
            return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        // Rendu du formulaire de modification
        return $this->render('vente/_form.edit.html.twig', [
            'vente' => $vente,
            'form' => $form->createView(),
            'details' => $details,
        ]);
    }

    #[Route('/{id}', name: 'app_vente_delete', methods: ['POST'])]
    public function delete(Request $request, Vente $vente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vente->getId(), $request->request->get('_token'))) {
            $entityManager->remove($vente);
            $entityManager->flush();

            $this->addFlash('delete', 'Le Vente a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_ventes', methods: ['GET'])]
    public function exportVentes(VenteRepository $venteRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Récupérez les ventes de la base de données
        $ventes = $venteRepository->findAll();
    
        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'Num facture');
        $sheet->setCellValue('B1', 'Client');
        $sheet->setCellValue('C1', 'Date vente');
        $sheet->setCellValue('D1', 'Total vente');
    
        // Écrivez les données dans la feuille de calcul
        $row = 2; // Start data from row 2 (after headers)
        foreach ($ventes as $vente) {
            $sheet->setCellValue('A' . $row, $vente->getNumfacture());
            $sheet->setCellValue('B' . $row, $vente->getIdClient()->getNom());
            $sheet->setCellValue('C' . $row, $vente->getDatevente());
            $sheet->setCellValue('D' . $row, $vente->total() . ' Ariary');
            $row++;
        }
    
        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'ventes');
        $writer->save($temp_file);
    
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'ventes.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/generatepdf/{id}', name: 'app_vente_pdf', methods: ['GET'])]
    #[ParamDecryptor(['id'])]
    public function generatePdf(Vente $vente, Request $request): Response
    {
        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();
        
        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        
        // Votre code existant pour le rendu HTML
        $html = $this->renderView('vente/facture.html.twig', [
            'vente' => $vente,
            'currentDateTime' => $formattedDateTime // Passer la date formatée à la vue Twig
        ]);

        $filename = sprintf('AeroSTOCK-vente-%s.pdf', $vente->getId());
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/vente/' . $filename;

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

    #[Route('/listpdfvente', name: 'app_vente_list', methods: ['GET'])]
    public function listPdf(VenteRepository $venteRepository, Request $request): Response
    {
        $vente = $venteRepository->findAll();

        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();

        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

        $html = $this->renderView('vente/pdf.html.twig', [
            'ventes' => $vente,
            'currentDateTime' => $formattedDateTime
        ]);

        $filename = sprintf('AeroSTOCK-vente.pdf');
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/vente/' . $filename;

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
