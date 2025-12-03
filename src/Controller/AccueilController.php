<?php
namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\EvenementRepository; // Ajout de l'import
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'app_accueil')]
    public function index(CategorieRepository $categorieRepository, EvenementRepository $evenementRepository): Response
    {
        // lire les catégories
        $lesCategories = $categorieRepository->findAll();
        
        // Récupérer les événements
        $derniersEvenementsPasses = $evenementRepository->findLastThreePastEvents();
        $prochainsEvenements = $evenementRepository->findNextThreeUpcomingEvents();
        
        return $this->render('accueil/index.html.twig', [
            'lesCategories' => $lesCategories,
            'derniersEvenementsPasses' => $derniersEvenementsPasses,
            'prochainsEvenements' => $prochainsEvenements,
        ]);
    }
}