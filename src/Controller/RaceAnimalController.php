<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\RaceAnimal;
use App\Form\RaceAnimalType;
use App\Repository\RaceAnimalRepository;
use Doctrine\ORM\EntityManagerInterface;

final class RaceAnimalController extends AbstractController
{
    #[Route('/race-animal', name: 'app_race_animal', methods: ['GET'])]
    public function index(RaceAnimalRepository $repository): Response
    {
        $raceAnimal = new RaceAnimal();
        $formCreation = $this->createForm(RaceAnimalType::class, $raceAnimal);
        // lire les races
        $lesRaces = $repository->findAll();
        return $this->render('raceAnimal/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesRaces' => $lesRaces,
            'idRaceAnimalModif' =>null,
            'formModification' =>null,
        ]);

    }

    #[Route('/race-animal/creer', name: 'app_race_animal_creer')]
    public function creerRaceAnimal(EntityManagerInterface $entityManager): Response
    {
        // créer une nouvelle race
        $raceAnimal = new RaceAnimal();
        $raceAnimal->setIntitule('Chien'); // exemple

        // persister et sauvegarder
        $entityManager->persist($raceAnimal);
        $entityManager->flush();

        return new Response('Nouvelle race créée : '.$raceAnimal->getIntitule().' (id '.$raceAnimal->getId().')');
    }

    #[Route('/race-animal/ajouter', name: 'app_race_animal_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, RaceAnimalRepository $repository): Response
    {
        $raceAnimal = new RaceAnimal();
        $form = $this->createForm(RaceAnimalType::class, $raceAnimal);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager->persist($raceAnimal);
            $entityManager->flush();

            $this->addFlash('success', 'La race animal ' . $raceAnimal->getIntitule() . ' a été ajouté.');
            return $this->redirectToRoute('app_race_animal');
        } else {
            // affichage de la liste des races avec le formulaire de création et ses erreurs
            // lire les races
            $lesRaces = $repository->findAll();
            // rendre la vue
            return $this->render('raceAnimal/index.html.twig', [
                'formCreation' => $form->createView(),
                'lesRaces' => $lesRaces,
                'formModification' => null,
                'idRaceAnimalModif' => null,
            ]);
        }

        // Formulaire non soumis ou invalide
        $lesRaces = $repository->findAll();

        return $this->render('raceAnimal/index.html.twig', [
            'formCreation' => $form->createView(),
            'lesRaces' => $lesRaces,
        ]);
    }

    #[Route('/race-animal/demandermodification/{id<\d+>}', name: 'app_race_animal_demandermodification', methods: ['GET'])]
    public function demanderModification(RaceAnimalRepository $repository, RaceAnimal $idRaceAnimalModif, Request $request): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $idRaceAnimalModif->getId(), $request->get('_token'))) {
            // créer l'objet et le formulaire de création
            $raceAnimal = new RaceAnimal();
            $formCreation = $this->createForm(RaceAnimalType::class, $raceAnimal);

            // on  crée le formulaire de modification
            $formModificationView = $this->createForm(RaceAnimalType::class, $idRaceAnimalModif)->createView();

            // lire les races
            $lesRaces = $repository->findAll();
            return $this->render('raceAnimal/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesRaces' => $lesRaces,
                'formModification' => $formModificationView,
                'idRaceAnimalModif' => $idRaceAnimalModif->getId(),
            ]);
        }
        return $this->redirectToRoute('app_race_animal');

    }

    #[Route('/race-animal/modifier/{id<\d+>}', name: 'app_race_animal_modifier', methods: ['POST'])]
    public function modifier(RaceAnimal $raceAnimal, Request $request, EntityManagerInterface $entityManager, RaceAnimalRepository $repository): Response
    // public function modifier(RaceAnimal $raceAnimal = null, $id = null, Request $request, EntityManagerInterface $entityManager, RaceAnimalType $repository)
    {
        //  Symfony 4 est capable de retrouver la race à l'aide de Doctrine ORM directement en utilisant l'id passé dans la route
        $form = $this->createForm(RaceAnimalType::class, $raceAnimal);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // if ($form->isSubmitted() && $form->isValid()) {
            // va effectuer la requête d'UPDATE en base de données
            // pas besoin de "persister" l'entité car l'objet a déjà été retrouvé à partir de Doctrine ORM.
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La race animal ' . $raceAnimal->getIntitule() . ' a été modifiée.'
            );
            // rediriger vers l'affichage des races qui comprend le formulaire pour l"ajout d'une nouvelle race
            return $this->redirectToRoute('app_race_animal');
        } else {
            // affichage de la liste des raes avec le formulaire de modification et ses erreurs
            // créer l'objet et le formulaire de création
            $raceAnimal = new RaceAnimal();
            $formCreation = $this->createForm(RaceAnimalType::class, $raceAnimal);
            // lire les races
            $lesRaces = $repository->findAll();
            // rendre la vue
            return $this->render('raceAnimal/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesRaces' => $lesRaces,
                'formModification' => $form->createView(),
                'idRaceAnimalModif' => $raceAnimal->getId(),
            ]);
        }
    }

    #[Route('/race-animal/supprimer/{id<\d+>}', name: 'app_race_animal_supprimer', methods: ['GET'])]
    public function supprimer(RaceAnimal $raceAnimal, Request $request, EntityManagerInterface $entityManager)
    {
        // vérifier le token
        if ($this->isCsrfTokenValid('action-item' . $raceAnimal->getId(), $request->get('_token'))) {
            // supprimer la race
            $entityManager->remove($raceAnimal);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La race ' . $raceAnimal->getIntitule() . ' a été supprimé.'
            );
        }
        return $this->redirectToRoute('app_race_animal');
    }
}
