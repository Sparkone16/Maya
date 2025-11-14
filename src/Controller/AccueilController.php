<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'app_accueil')]
    public function index(CategorieRepository $repository): Response
    {
        // lire les catégories
        $lesCategories = $repository->findAll();
        return $this->render('accueil/index.html.twig', [
            'lesCategories' => $lesCategories,
        ]);
    }
}
