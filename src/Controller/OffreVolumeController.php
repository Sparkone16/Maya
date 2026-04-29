<?php

namespace App\Controller;

use App\Entity\OffreVolume;
use App\Form\OffreVolumeType;
use App\Repository\OffreVolumeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

final class OffreVolumeController extends AbstractController
{
    #[Route('/offreVolume', name: 'app_offreVolume', methods: ['GET'])]
    public function index(Request $request, OffreVolumeRepository $repository, PaginatorInterface $paginator): Response
    {
        $offreVolume = new OffreVolume();
        $formCreation = $this->createForm(OffreVolumeType::class, $offreVolume);

        $lesOffres = $paginator->paginate(
            $repository->findAll(),
            $request->query->getint('page', 1),
            5
        );
        return $this->render('offreVolume/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesOffres' => $lesOffres,
            'idOffreVolumeModif' => null,
            'formModification' => null,
        ]);
    }


    // Test avec valeurs prédéfinies
    // #[Route('/offreVolume/creer', name: 'app_offreVolume_creer')]
    // public function creerOffreVolume(EntityManagerInterface $entityManager): Response
    // {
    //     $offreVolume = new OffreVolume();
    //     $offreVolume->setNom('Rex');
    //     $offreVolume->setRace('Berger allemand');
    //     $offreVolume->setDateNaissance(new \DateTime('2020-06-15'));

    //     $race = $entityManager->getRepository(RaceOffreVolume::class)->findOneBy(['intitule' => 'Chien']);

    //     if (!$race) {
    //         return new Response('Erreur : aucune race "Chien" trouvée.');
    //     }

    //     $offreVolume->setRaceOffreVolume($race);

    //     $entityManager->persist($offreVolume);
    //     $entityManager->flush();

    //     return new Response('Nouvel offreVolume créé : ' . $offreVolume->getNom() . ' (id ' . $offreVolume->getId() . ') — Race : ' . $race->getIntitule());
    // }

    #[Route('/offreVolume/ajouter', name: 'app_offreVolume_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager, OffreVolumeRepository $repository): Response
    {
        $offreVolume = new OffreVolume();
        $form = $this->createForm(OffreVolumeType::class, $offreVolume);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager->persist($offreVolume);
            $entityManager->flush();

            $this->addFlash('success', 'L’offre de volume pour le produit' . $offreVolume->getProduits()->getLibelle() . ' a été ajouté.');
            return $this->redirectToRoute('app_offreVolume');
        } else {
            // affichage de la liste des animaux avec le formulaire de création et ses erreurs
            // lire les animaux
            $lesOffres = $paginator->paginate(
                $repository->findAll(),
                $request->query->getint('page', 1),
                5
            );
            // rendre la vue
            return $this->render('offreVolume/index.html.twig', [
                'formCreation' => $form->createView(),
                'lesOffres' => $lesOffres,
                'formModification' => null,
                'idOffreVolumeModif' => null,
            ]);
        }

        // Formulaire non soumis ou invalide
        $lesOffres = $repository->findAll();

        return $this->render('offreVolume/index.html.twig', [
            'formCreation' => $form->createView(),
            'lesOffres' => $lesOffres,
        ]);
    }

    #[Route('/offreVolume/demandermodification/{id<\d+>}', name: 'app_offreVolume_demandermodification', methods: ['GET'])]
    public function demanderModification(Request $request, OffreVolumeRepository $repository, PaginatorInterface $paginator, OffreVolume $offreVolumeModif): Response
    {
        // créer l'objet et le formulaire de création
        $offreVolume = new OffreVolume();
        $formCreation = $this->createForm(OffreVolumeType::class, $offreVolume);

        // on  crée le formulaire de modification
        $formModificationView = $this->createForm(OffreVolumeType::class, $offreVolumeModif)->createView();

        // lire les animaux
        $lesOffres = $paginator->paginate(
            $repository->findAll(),
            $request->query->getint('page', 1),
            5
        );
        return $this->render('offreVolume/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesOffres' => $lesOffres,
            'formModification' => $formModificationView,
            'idOffreVolumeModif' => $offreVolumeModif->getId(),
        ]);
    }

    #[Route('/offreVolume/modifier/{id<\d+>}', name: 'app_offreVolume_modifier', methods: ['POST'])]
    public function modifier(OffreVolume $offreVolume, PaginatorInterface $paginator, Request $request, EntityManagerInterface $entityManager, OffreVolumeRepository $repository): Response
    // public function modifier(OffreVolume $offreVolume = null, $id = null, Request $request, EntityManagerInterface $entityManager, OffreVolumeRepository $repository)
    {
        //  Symfony 4 est capable de retrouver l'offreVolume à l'aide de Doctrine ORM directement en utilisant l'id passé dans la route
        $form = $this->createForm(OffreVolumeType::class, $offreVolume);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // if ($form->isSubmitted() && $form->isValid()) {
            // va effectuer la requête d'UPDATE en base de données
            // pas besoin de "persister" l'entité car l'objet a déjà été retrouvé à partir de Doctrine ORM.
            $entityManager->flush();
            $this->addFlash(
                'success',
                'L\'offre de volume pour le produit' . $offreVolume->getProduits()->getLibelle() . ' a été modifiée.'
            );
            // rediriger vers l'affichage des animaux qui comprend le formulaire pour l"ajout d'un nouvel offreVolume
            return $this->redirectToRoute('app_offreVolume');
        } else {
            // affichage de la liste des animaux avec le formulaire de modification et ses erreurs
            // créer l'objet et le formulaire de création
            $offreVolume = new OffreVolume();
            $formCreation = $this->createForm(OffreVolumeType::class, $offreVolume);
            // lire les animaux
            $lesOffres = $paginator->paginate(
                $repository->findAll(),
                $request->query->getint('page', 1),
                5
            );
            // rendre la vue
            return $this->render('offreVolume/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesOffres' => $lesOffres,
                'formModification' => $form->createView(),
                'idOffreVolumeModif' => $offreVolume->getId(),
            ]);
        }
    }

    #[Route('/offreVolume/supprimer/{id<\d+>}', name: 'app_offreVolume_supprimer', methods: ['GET'])]
    public function supprimer(OffreVolume $offreVolume, Request $request, EntityManagerInterface $entityManager)
    {
        // vérifier le token
        if ($this->isCsrfTokenValid('action-item' . $offreVolume->getId(), $request->get('_token'))) {

            // supprimer l'offreVolume
            $entityManager->remove($offreVolume);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'L\'offre de volume pour le produit ' . $offreVolume->getProduits()->getLibelle() . ' a été supprimé.'
            );
        }
        return $this->redirectToRoute('app_offreVolume');
    }
}
