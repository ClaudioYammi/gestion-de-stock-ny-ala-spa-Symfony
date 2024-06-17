<?php

namespace App\Controller;

use App\Entity\DetailAchat;
use App\Entity\DetailVente;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AchatRepository;
use App\Repository\CategorieRepositoryion;
use App\Repository\CommandeRepository;
use App\Repository\FournisseurRepository;
use App\Repository\VenteRepository;
use App\Repository\DetailAchatRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(
        AchatRepository $achatRepository, 
        CategorieRepository $categoryRepository, 
        ProduitRepository $produitRepository, 
        VenteRepository $venteRepository,
        EntityManagerInterface $entityManager
    ): Response

    {
        $detailAchatRepository = $entityManager->getRepository(DetailAchat::class);
        $totalDesAchats = DetailAchat::getTotalDesAchats($detailAchatRepository);

        $detailVenteRepository = $entityManager->getRepository(DetailVente::class);
        $totalDesVentes = DetailVente::getTotalDesVentes($detailVenteRepository);

        $achatCount = $achatRepository->count([]);
        $venteCount = $venteRepository->count([]);
        $categorieCount = $categoryRepository->count([]);
        $ProduitCount = $produitRepository->count([]);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'achats'         => $achatCount,
            'ventes'         => $venteCount,
            'categories'      => $categorieCount,
            'produits'       => $ProduitCount,
            'totalDesAchats' => $totalDesAchats,
            'totalDesVentes' => $totalDesVentes,
        ]);
    }

    #[Route('/api/monthly-statistics', name: 'monthly_statistics', methods: ['GET'])]
    public function getMonthlyStatistics(Request $request, VenteRepository $venteRepository, AchatRepository $achatRepository): JsonResponse
    {
        // Get the year from query parameters, default to current year if not provided
        $year = $request->query->getInt('year', date('Y'));

        $ventes = $venteRepository->findAll();
        $achats = $achatRepository->findAll();

        $salesData = $this->calculateMonthlyTotalsForYear($ventes, 'datevente', $year);
        $purchaseData = $this->calculateMonthlyTotalsForYear($achats, 'dateachat', $year);

        // Debugging: Log the data
        error_log('Sales Data for ' . $year . ': ' . print_r($salesData, true));
        error_log('Purchase Data for ' . $year . ': ' . print_r($purchaseData, true));

        return new JsonResponse([
            'sales' => $salesData,
            'purchases' => $purchaseData
        ]);
    }

    private function calculateMonthlyTotalsForYear($entities, $dateField, $year)
    {
        $totals = [];

        // Initialize all months for the specified year
        for ($month = 1; $month <= 12; $month++) {
            $monthKey = sprintf('%04d-%02d', $year, $month);
            $totals[$monthKey] = 0;
        }

        // Calculate totals for each month
        foreach ($entities as $entity) {
            $date = $entity->{'get' . ucfirst($dateField)}();
            if ($date && $date->format('Y') == $year) {
                $month = $date->format('Y-m');
                $totals[$month] += $entity->total();  // Assuming total() method gives the required value
            }
        }

        ksort($totals); // Sort by month

        return $totals;
    }

    
    
}
