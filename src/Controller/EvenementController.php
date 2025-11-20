<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

final class EvenementController extends AbstractController
{
    #[Route('/evenement', name: 'app_evenement', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $repository, PaginatorInterface $paginator ): Response
    {
        // créer l'objet et le formulaire de création
        $Evenement= new Evenement();
        $formCreation = $this->createForm(EvenementType::class, $Evenement);

        // Pagination
        $lesEvenements = $paginator->paginate(
            $repository->findAll(),
            $request->query->getint('page', 1),
            5
        );
        return $this->render('evenement/index.html.twig', [
            'formCreation' => $formCreation-> createView(),
            'lesEvenements' => $lesEvenements,
            'formModification' => null,
            'idEvenementModif' => null,

        ]);
    }
        #[Route('/evenement/ajouter', name: 'app_evenement_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, EvenementRepository $repository): Response

    {
        //  $Evenement objet de la classe Evenement, il contiendra les valeurs saisies dans les champs après soumission du formulaire.
        //  $request  objet avec les informations de la requête HTTP (GET, POST, ...)
        //  $entityManager  pour la persistance des données

        // création d'un formulaire de type EvenementType
        $Evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $Evenement);

        // handleRequest met à jour le formulaire
        //  si le formulaire a été soumis, handleRequest renseigne les propriétés
        //      avec les données saisies par l'utilisateur et retournées par la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // c'est le cas du retour du formulaire
            //         l'objet $Evenement a été automatiquement "hydraté" par Doctrine
            // dire à Doctrine que l'objet sera (éventuellement) persisté
            $entityManager->persist($Evenement);
            // exécuter les requêtes (indiquées avec persist) ici il s'agit de l'ordre INSERT qui sera exécuté
            $entityManager->flush();
            // ajouter un message flash de succès pour informer l'utilisateur
            $this->addFlash(
                'success',
                'L\'évènement' . $Evenement->getTitre() . ' a été ajoutée.'
            );
            // rediriger vers l'affichage des évènements qui comprend le formulaire pour l"ajout d'une nouvelle évènements
            return $this->redirectToRoute('app_evenement');
        } else {
            // affichage de la liste des évènements avec le formulaire de création et ses erreurs
            // lire les évènements
            $lesEvenements = $repository->findAll();
            // rendre la vue
            return $this->render('evenement/index.html.twig', [
                'formCreation' => $form->createView(),
                'lesEvenements' => $lesEvenements,
                'formModification' => null,
                'idEvenementModif' => null,
            ]);
        }
    }
    #[Route('/evenement/demandermodification/{id<\d+>}', name: 'app_evenement_demandermodification', methods: ['GET'])]
    public function demanderModification(EvenementRepository $repository, Evenement $EvenementModif, Request $request): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $EvenementModif->getId(), $request->get('_token'))) {
        // créer l'objet et le formulaire de création
        $Evenement = new Evenement();
        $formCreation = $this->createForm(EvenementType::class, $Evenement);

        // on  crée le formulaire de modification
        $formModificationView = $this->createForm(EvenementType::class, $EvenementModif)->createView();

        // lire les évènements
        $lesEvenements = $repository->findAll();
        return $this->render('evenement/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesEvenements' => $lesEvenements,
            'formModification' => $formModificationView,
            'idEvenementModif' => $EvenementModif->getId(),
        ]);
    }
        return $this->redirectToRoute('app_evenement');
    }

    #[Route('/evenement/modifier/{id<\d+>}', name: 'app_evenement_modifier', methods: ['POST'])]
    public function modifier(Evenement $Evenement, Request $request, EntityManagerInterface $entityManager, EvenementRepository $repository): Response
    // public function modifier(Evenement $Evenement = null, $id = null, Request $request, EntityManagerInterface $entityManager, EvenementRepository $repository)
    {
        //  Symfony 4 est capable de retrouver la évènements à l'aide de Doctrine ORM directement en utilisant l'id passé dans la route
        $form = $this->createForm(EvenementType::class, $Evenement);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // if ($form->isSubmitted() && $form->isValid()) {
            // va effectuer la requête d'UPDATE en base de données
            // pas besoin de "persister" l'entité car l'objet a déjà été retrouvé à partir de Doctrine ORM.
            $entityManager->flush();
            $this->addFlash(
                'success',
                'L\'évènement ' . $Evenement->getTitre() . ' a été modifiée.'
            );
            // rediriger vers l'affichage des évènements qui comprend le formulaire pour l"ajout d'une nouvelle évènement
            return $this->redirectToRoute('app_evenement');
        } else {
            // affichage de la liste des évènements avec le formulaire de modification et ses erreurs
            // créer l'objet et le formulaire de création
            $Evenement = new Evenement();
            $formCreation = $this->createForm(EvenementType::class, $Evenement);
            // lire les évènements
            $lesEvenements = $repository->findAll();
            // rendre la vue
            return $this->render('evenement/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesEvenements' => $lesEvenements,
                'formModification' => $form->createView(),
                'idEvenementModif' => $Evenement->getId(),
            ]);
        }
    }
        #[Route('/evenement/supprimer/{id<\d+>}', name: 'app_evenement_supprimer', methods: ['GET'])]
    public function supprimer(Evenement $Evenement, Request $request, EntityManagerInterface $entityManager)
    {
            // supprimer l'évènement
            $entityManager->remove($Evenement);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'L\'évènement' . $Evenement->getTitre() . ' a été supprimée.'
            );
        
        return $this->redirectToRoute('app_evenement');
    }

}