<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
#[Route('/client')]
class ClientController extends AbstractController
{
    private $knpSnappyPdf;

    public function __construct( Pdf $knpSnappyPdf, EntityManagerInterface $entityManager)
    {
        $this->knpSnappyPdf = $knpSnappyPdf;
    }
    
    #[Route('/', name: 'app_client_index', methods: ['GET'])]
    public function index(Request $request, 
            ClientRepository $clientRepository, 
            PaginatorInterface $paginator, 
            EntityManagerInterface $entityManager
            ): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        // filtrage ------------------------------------------------------------------------------------------------
        $sort = $request->query->get('sort'); // Get sort parameter
        $order = $request->query->get('order'); // Get order parameter
        
        // recherche ------------------------------------------------------------------------------------------------
        $searchCriteria = []; // Initialize empty search criteria
        $orderBy = null; // Initialize empty order by

        // Get search parameters from request query (if any)
        $allowedAttributes = ['nom', 'prenom', 'telephone', 'email']; // Define allowed attributes
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
            $clientRepository->findByCriteriaAllowed($searchCriteria, $orderBy),
            $request->query->getInt('page', 1),
            5 // Number of elements per page
        );

        return $this->render('client/index.html.twig', [
            'pagination' => $pagination,
            'searchCriteria' => $searchCriteria, // Pass search criteria to Twig template for display
            'allowedAttributes' => $allowedAttributes,
            'order' => $order,
            'sort' => $sort,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            // Vérifiez si le client est lié à d'autres données
            if (count($client->getVentes()) > 0) {
                $this->addFlash('error', 'Impossible de supprimer ce client car il est lié à d\'autres données dans la table vente.');
            } else {
                try {
                    $entityManager->remove($client);
                    $entityManager->flush();
                    $this->addFlash('delete', 'Le client a été supprimé avec succès.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur s\'est cliente lors de la suppression du client.');
                }
            }
        }

        return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_clients', methods: ['GET'])]
    public function exportClients(ClientRepository $clientRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Récupérez les clients de la base de données
        $clients = $clientRepository->findAll();

        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Prenom');
        $sheet->setCellValue('D1', 'Adresse');
        $sheet->setCellValue('E1', 'Telephone');
        $sheet->setCellValue('F1', 'Email');
    
        // Écrivez les données dans la feuille de calcul
        $row = 1; // Start data from row 2 (after headers)

        // Écrivez les données dans la feuille de calcul
        foreach ($clients as $client) {
            $sheet->setCellValue('A' . ($row+1), $client->getId());
            $sheet->setCellValue('B' . ($row+1), $client->getNom());
            $sheet->setCellValue('C' . ($row+1), $client->getprenom());
            $sheet->setCellValue('D' . ($row+1), $client->getAdresse());
            $sheet->setCellValue('E' . ($row+1), $client->gettelephone());
            $sheet->setCellValue('F' . ($row+1), $client->getemail());
            $row++;

        }

        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'clients');
        $writer->save($temp_file);

        $this->addFlash('export_xls', 'Exportation en excel términée');
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'clients.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/generatepdf/{id}', name: 'app_client_generate_pdf', methods: ['GET'])]
    public function generatePdf(Client $client, Request $request): Response
    {
        $html = $this->renderView('client/facture.html.twig', [
            'client' => $client,
        ]);

        $filename = sprintf('client-%s.pdf', $client->getId());
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/client/' . $filename;

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

    #[Route('/listpdf', name: 'app_client_list', methods: ['GET'])]
    public function listPdf(ClientRepository $clientRepository,Request $request): Response
    {
        $client = $clientRepository->findAll();

        // Créer un objet DateTime pour la date actuelle
        $dateTime = new \DateTime();

        // Ajouter 3 heures au fuseau horaire actuel
        $dateTime->modify('+3 hours');
        
        // Formater la date selon vos besoins
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

        $html = $this->renderView('client/pdf.html.twig', [
            'clients' => $client,
            'currentDateTime' => $formattedDateTime // Passer la date formatée à la vue Twig
            
        ]);

        $filename = sprintf('AeroSTOCK-client.pdf');
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/pdf/client/' . $filename;

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
