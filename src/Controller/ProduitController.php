<?php

namespace App\Controller;

use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ProduitRecherche;
use App\Form\ProduitRechercheType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Knp\Component\Pager\PaginatorInterface;



final class ProduitController extends AbstractController
{

    #[Route('/produit', name: 'app_produit', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $repository, SessionInterface $session, PaginatorInterface $paginator): Response
    {
        // créer l'objet et le formulaire de recherche
        $produitRecherche = new ProduitRecherche();
        // form en GET : Symfony lira les paramètres depuis $request->query
        $formRecherche = $this->createForm(ProduitRechercheType::class, $produitRecherche, [
            'method' => 'GET',
            // optionnel : désactiver CSRF pour formulaires GET 
            // 'csrf_protection' => false,
        ]);
        $formRecherche->handleRequest($request);
        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $produitRecherche = $formRecherche->getData();
            // cherche les produits correspondant aux critères, triés par libellé
            // requête construite dynamiquement alors il est plus simple d'utiliser le querybuilder
            $lesProduits = $repository->findAllByCriteria($produitRecherche);
        } else {
            $lesProduits = $repository->findAllOrderByLibelle();
        }

        $lesProduits = $paginator->paginate(
            $lesProduits,
            $request->query->getint('page', 1),
            5
        );
        return $this->render('produit/index.html.twig', [
            'formRecherche' => $formRecherche,
            'lesProduits' => $lesProduits,
        ]);
    }


    #[Route('/produit/ajouter', name: 'app_produit_ajouter', methods: ['POST', 'GET'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // cas où le formulaire d'ajout a été soumis par l'utilisateur 
            $produit = $form->getData();
            // on met à jour la base de données 
            $entityManager->persist($produit);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Le produit ' . $produit->getLibelle() . ' a été ajouté.'
            );
            // rediriger vers l'URL de retour (liste avec page et filtres)  
            return $this->redirectToRoute('app_produit', $request->query->all());

        } else {
            // cas où l'utilisateur a demandé l'ajout, on affiche le formulaire d'ajout
            return $this->render('produit/ajouter.html.twig', [
                'form' => $form,
            ]);
        }
    }

    #[Route('/produit/modifier/{id<\d+>}', name: 'app_produit_modifier', methods: ['POST', 'GET'])]
    public function modifier(Produit $produit, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // cas où le formulaire  a été soumis par l'utilisateur et est valide
            //pas besoin de "persister" l'entité : en effet, l'objet a déjà été retrouvé à partir de Doctrine ORM.
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Le produit ' . $produit->getLibelle() . ' a été modifié.'
            );

            // rediriger vers l'URL de retour (liste avec page et filtres)  
            return $this->redirectToRoute('app_produit', $request->query->all());

        }
        // cas où l'utilisateur a demandé la modification, on affiche le formulaire pour la modification
        return $this->render('produit/modifier.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/produit/supprimer/{id<\d+>}', name: 'app_produit_supprimer', methods: ['GET'])]
    public function supprimer(Produit $produit, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $produit->getId(), $request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Le produit ' . $produit->getLibelle() . ' a été supprimé.'
            );
        }
        // rediriger vers l'URL de retour (liste avec page et filtres)  
        return $this->redirectToRoute('app_produit', $request->query->all());

    }
}
