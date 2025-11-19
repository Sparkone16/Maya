<?php

namespace App\Controller;

use App\Entity\Saisonnier;
use App\Form\SaisonnierType;
use App\Repository\SaisonnierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


final class SaisonnierController extends AbstractController
{
    #[Route('/saisonnier', name: 'app_saisonnier', methods: ['GET'])]
    public function index(Request $request, SaisonnierRepository $repository, PaginatorInterface $paginator): Response
    {
        // créer l'objet et le formulaire de création
        $saisonnier = new Saisonnier();
        $formCreation = $this->createForm(SaisonnierType::class, $saisonnier);

        // Pagination
        $lesSaisonniers = $paginator->paginate(
            $repository->findAll(),
            $request->query->getint('page', 1),
            5
        );
        // lire les saisonniers
        return $this->render('saisonnier/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesSaisonniers' => $lesSaisonniers,
            'idSaisonnierModif' => null,
            'formModification' => null,
        ]);
    }


    #[Route('/saisonnier/ajouter', name: 'app_saisonnier_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, SaisonnierRepository $repository): Response

    {
        //  $saisonnier objet de la classe Saisonnier, il contiendra les valeurs saisies dans les champs après soumission du formulaire.
        //  $request  objet avec les informations de la requête HTTP (GET, POST, ...)
        //  $entityManager  pour la persistance des données

        // création d'un formulaire de type SaisonnierType
        $saisonnier = new Saisonnier();
        $form = $this->createForm(SaisonnierType::class, $saisonnier);

        // handleRequest met à jour le formulaire
        //  si le formulaire a été soumis, handleRequest renseigne les propriétés
        //      avec les données saisies par l'utilisateur et retournées par la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // c'est le cas du retour du formulaire
            //         l'objet $saisonnier a été automatiquement "hydraté" par Doctrine
            // dire à Doctrine que l'objet sera (éventuellement) persisté
            $entityManager->persist($saisonnier);
            // exécuter les requêtes (indiquées avec persist) ici il s'agit de l'ordre INSERT qui sera exécuté
            $entityManager->flush();
            // ajouter un message flash de succès pour informer l'utilisateur
            $this->addFlash(
                'success',
                'La saisonnier ' . $saisonnier->getNom() . ' a été ajoutée.'
            );
            // rediriger vers l'affichage des saisonniers qui comprend le formulaire pour l"ajout d'une nouvelle saisonnier
            return $this->redirectToRoute('app_saisonnier');
        } else {
            // affichage de la liste des saisonniers avec le formulaire de création et ses erreurs
            // lire les saisonniers
            $lesSaisonniers = $repository->findAll();
            // rendre la vue
            return $this->render('saisonnier/index.html.twig', [
                'formCreation' => $form->createView(),
                'lesSaisonniers' => $lesSaisonniers,
                'formModification' => null,
                'idSaisonnierModif' => null,
            ]);
        }
    }

    #[Route('/saisonnier/demandermodification/{id<\d+>}', name: 'app_saisonnier_demandermodification', methods: ['GET'])]
    public function demanderModification(SaisonnierRepository $repository, Saisonnier $saisonnierModif, Request $request): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $saisonnierModif->getId(), $request->get('_token'))) {
            // créer l'objet et le formulaire de création
            $saisonnier = new Saisonnier();
            $formCreation = $this->createForm(SaisonnierType::class, $saisonnier);

            // on  crée le formulaire de modification
            $formModificationView = $this->createForm(SaisonnierType::class, $saisonnierModif)->createView();

            // lire les saisonniers
            $lesSaisonniers = $repository->findAll();
            return $this->render('saisonnier/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesSaisonniers' => $lesSaisonniers,
                'formModification' => $formModificationView,
                'idSaisonnierModif' => $saisonnierModif->getId(),
            ]);
        }
        return $this->redirectToRoute('app_saisonnier');

    }

    #[Route('/saisonnier/modifier/{id<\d+>}', name: 'app_saisonnier_modifier', methods: ['POST'])]
    public function modifier(Saisonnier $saisonnier, Request $request, EntityManagerInterface $entityManager, SaisonnierRepository $repository): Response
    // public function modifier(Saisonnier $saisonnier = null, $id = null, Request $request, EntityManagerInterface $entityManager, SaisonnierRepository $repository)
    {
        //  Symfony 4 est capable de retrouver la saisonnier à l'aide de Doctrine ORM directement en utilisant l'id passé dans la route
        $form = $this->createForm(SaisonnierType::class, $saisonnier);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // if ($form->isSubmitted() && $form->isValid()) {
            // va effectuer la requête d'UPDATE en base de données
            // pas besoin de "persister" l'entité car l'objet a déjà été retrouvé à partir de Doctrine ORM.
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La saisonnier ' . $saisonnier->getNom() . ' a été modifiée.'
            );
            // rediriger vers l'affichage des saisonniers qui comprend le formulaire pour l"ajout d'une nouvelle saisonnier
            return $this->redirectToRoute('app_saisonnier');
        } else {
            // affichage de la liste des saisonniers avec le formulaire de modification et ses erreurs
            // créer l'objet et le formulaire de création
            $saisonnier = new Saisonnier();
            $formCreation = $this->createForm(SaisonnierType::class, $saisonnier);
            // lire les saisonniers
            $lesSaisonniers = $repository->findAll();
            // rendre la vue
            return $this->render('saisonnier/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesSaisonniers' => $lesSaisonniers,
                'formModification' => $form->createView(),
                'idSaisonnierModif' => $saisonnier->getId(),
            ]);
        }
    }


    #[Route('/saisonnier/supprimer/{id<\d+>}', name: 'app_saisonnier_supprimer')]
    public function supprimer(Saisonnier $saisonnier, Request $request, EntityManagerInterface $entityManager)
    {
        // vérifier le token
        if ($this->isCsrfTokenValid('action-item' . $saisonnier->getId(), $request->get('_token'))) {
            // supprimer la saisonnier
            $entityManager->remove($saisonnier);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La saisonnier ' . $saisonnier->getNom() . ' a été supprimée.'
            );
        }
        return $this->redirectToRoute('app_saisonnier');
    }
}