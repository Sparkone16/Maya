<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Recette;
use App\Repository\RecetteRepository;
use App\Form\RecetteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;



final class RecetteController extends AbstractController
{
    #[Route('/recette', name: 'app_recette')]
    public function index(Request $request, RecetteRepository $repository, PaginatorInterface $paginator): Response
    {

        $lesRecettes = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('recette/index.html.twig', [
            'lesRecettes' => $lesRecettes,
        ]);
    }

    #[Route('/recette/ajouter', name: 'app_recette_ajouter', methods: ['POST', 'GET'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // cas où le formulaire d'ajout a été soumis par l'utilisateur 
            $recette = $form->getData();
            // on met à jour la base de données 
            $entityManager->persist($recette);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La recette ' . $recette->getNom() . ' a été ajouté.'
            );
            return $this->redirectToRoute('app_recette');
        } else {
            // cas où l'utilisateur a demandé l'ajout, on affiche le formulaire d'ajout
            return $this->render('recette/ajouter.html.twig', [
                'form' => $form,
            ]);
        }
    }

    #[Route('/recette/modifier/{id<\d+>}', name: 'app_recette_modifier', methods: ['POST', 'GET'])]
    public function modifier(Recette $recette, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // cas où le formulaire  a été soumis par l'utilisateur et est valide
            //pas besoin de "persister" l'entité : en effet, l'objet a déjà été retrouvé à partir de Doctrine ORM.
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La recette ' . $recette->getNom() . ' a été modifié.'
            );

            return $this->redirectToRoute('app_recette');
        }
        // cas où l'utilisateur a demandé la modification, on affiche le formulaire pour la modification
        return $this->render('recette/modifier.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/recette/supprimer/{id<\d+>}', name: 'app_recette_supprimer', methods: ['GET'])]
    public function supprimer(Recette $recette, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $recette->getId(), $request->get('_token'))) {
            $entityManager->remove($recette);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La recette ' . $recette->getNom() . ' a été supprimé.'
            );
        }
        return $this->redirectToRoute('app_recette');
    }
}
