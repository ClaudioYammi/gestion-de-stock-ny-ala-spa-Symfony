<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
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

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(Request $request, 
        UserRepository $userRepository, 
        PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
        $userRepository->findAll(),
        $request->query->getInt('page', 1),
        5 // Nombre d'éléments par page
        );

        return $this->render('user/index.html.twig', [
        'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    #[ParamDecryptor(['id'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[ParamDecryptor(['id'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    #[ParamDecryptor(['id'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('delete', 'l\'utilisateur a été supprimé avec succès.');
            
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'export_users', methods: ['GET'])]
    public function exportUsers(UserRepository $userRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Récupérez les users de la base de données
        $users = $userRepository->findAll();
    
        // Définissez les titres des colonnes
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Pseudo');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Created At');
        $sheet->setCellValue('E1', 'Roles');
    
        // Écrivez les données dans la feuille de calcul
        $row = 2; // Start data from row 2 (after headers)
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->getId());
            $sheet->setCellValue('B' . $row, $user->getPseudo());
            $sheet->setCellValue('C' . $row, $user->getEmail());
    
            // Option 1: Use a Getter Method (recommended)
            $sheet->setCellValue('D' . $row, $user->getCreatedAt()); // Call the getter method
    
            $sheet->setCellValue('E' . $row, implode(', ', $user->getRoles())); // Join roles as a string
            $row++;
        }
    
        // Créez un écrivain et écrivez la feuille de calcul dans un fichier temporaire
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'users');
        $writer->save($temp_file);
    
        // Retournez le fichier en tant que téléchargement
        return $this->file($temp_file, 'users.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
    
}
