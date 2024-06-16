<?php

namespace App\Controller;

use App\Entity\Fournisseur;
use App\Form\FournisseurType;
use App\Repository\FournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;

#[Route('/fournisseur')]
class FournisseurController extends AbstractController
{
    #[Route('/', name: 'app_fournisseur_index', methods: ['GET'])]
    public function index(Request $request, 
            FournisseurRepository $produitRepository, 
            PaginatorInterface $paginator): Response
        {
        $pagination = $paginator->paginate(
        $produitRepository->findAll(),
        $request->query->getInt('page', 1),
        5 // Nombre d'éléments par page
        );

        return $this->render('fournisseur/index.html.twig', [
        'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_fournisseur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fournisseur = new Fournisseur();
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fournisseur);
            $entityManager->flush();

            $this->addFlash('success', 'Categrie a été effectuer avec succès.');
            return $this->redirectToRoute('app_fournisseur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fournisseur/new.html.twig', [
            'fournisseur' => $fournisseur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fournisseur_show', methods: ['GET'])]
    #[ParamDecryptor(['id'])]
    public function show(Fournisseur $fournisseur): Response
    {
        return $this->render('fournisseur/show.html.twig', [
            'fournisseur' => $fournisseur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fournisseur_edit', methods: ['GET', 'POST'])]
    #[ParamDecryptor(['id'])]
    public function edit(Request $request, Fournisseur $fournisseur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('edit', 'Achat a été modifier avec succès.');
            return $this->redirectToRoute('app_fournisseur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fournisseur/edit.html.twig', [
            'fournisseur' => $fournisseur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fournisseur_delete', methods: ['POST'])]
    #[ParamDecryptor(['id'])]
    public function delete(Request $request, Fournisseur $fournisseur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fournisseur->getId(), $request->request->get('_token'))) {
            // Vérifiez si le fournisseur est lié à d'autres données
            if (count($fournisseur->getAchats()) > 0) {
                $this->addFlash('error', 'Impossible de supprimer ce fournisseur car il est lié à d\'autres données dans la table achat.');
            } else {
                try {
                    $entityManager->remove($fournisseur);
                    $entityManager->flush();
                    $this->addFlash('success', 'Le fournisseur a été supprimé avec succès.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du fournisseur.');
                }
            }
        }

        return $this->redirectToRoute('app_fournisseur_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_fournisseurs', methods: ['GET'])]
    public function exportfournisseurs(FournisseurRepository $fournisseurRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Récupérez les fournisseurs de la base de données
        $fournisseurs = $fournisseurRepository->findAll();
    
        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Adresse');
        $sheet->setCellValue('E1', 'telephone');

        // Écrivez les données dans la feuille de calcul
        $row = 2; // Start data from row 2 (after headers)
        foreach ($fournisseurs as $fournisseur) {
            $sheet->setCellValue('A' . $row, $fournisseur->getId());
            $sheet->setCellValue('B' . $row, $fournisseur->getNom());
            $sheet->setCellValue('C' . $row, $fournisseur->getEmail());
            $sheet->setCellValue('D' . $row, $fournisseur->getAdresse());
            $sheet->setCellValue('E' . $row, $fournisseur->getTelephone());
            $row++;
        }
    
        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'fournisseurs');
        $writer->save($temp_file);
    
        $this->addFlash('export_xls', 'Exportation en excel términée');
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'fournisseurs.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
