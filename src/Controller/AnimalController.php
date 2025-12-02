<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\RaceAnimal;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

final class AnimalController extends AbstractController
{
    #[Route('/animal', name: 'app_animal', methods: ['GET'])]
    public function index(Request $request, AnimalRepository $repository, PaginatorInterface $paginator): Response
    {
        $animal = new Animal();
        $formCreation = $this->createForm(AnimalType::class, $animal);

        $lesAnimaux = $paginator->paginate(
            $repository->findAll(),
            $request->query->getint('page', 1),
            5
        );
        return $this->render('animal/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesAnimaux' => $lesAnimaux,
            'idAnimalModif' => null,
            'formModification' => null,
        ]);
    }


    // Test avec valeurs prédéfinies
    // #[Route('/animal/creer', name: 'app_animal_creer')]
    // public function creerAnimal(EntityManagerInterface $entityManager): Response
    // {
    //     $animal = new Animal();
    //     $animal->setNom('Rex');
    //     $animal->setRace('Berger allemand');
    //     $animal->setDateNaissance(new \DateTime('2020-06-15'));

    //     $race = $entityManager->getRepository(RaceAnimal::class)->findOneBy(['intitule' => 'Chien']);

    //     if (!$race) {
    //         return new Response('Erreur : aucune race "Chien" trouvée.');
    //     }

    //     $animal->setRaceAnimal($race);

    //     $entityManager->persist($animal);
    //     $entityManager->flush();

    //     return new Response('Nouvel animal créé : ' . $animal->getNom() . ' (id ' . $animal->getId() . ') — Race : ' . $race->getIntitule());
    // }

    #[Route('/animal/ajouter', name: 'app_animal_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager, AnimalRepository $repository): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager->persist($animal);
            $entityManager->flush();

            $this->addFlash('success', 'L’animal ' . $animal->getNom() . ' a été ajouté.');
            return $this->redirectToRoute('app_animal');
        } else {
            // affichage de la liste des animaux avec le formulaire de création et ses erreurs
            // lire les animaux
            $lesAnimaux = $paginator->paginate(
                $repository->findAll(),
                $request->query->getint('page', 1),
                5
            );
            // rendre la vue
            return $this->render('animal/index.html.twig', [
                'formCreation' => $form->createView(),
                'lesAnimaux' => $lesAnimaux,
                'formModification' => null,
                'idAnimalModif' => null,
            ]);
        }

        // Formulaire non soumis ou invalide
        $lesAnimaux = $repository->findAll();

        return $this->render('animal/index.html.twig', [
            'formCreation' => $form->createView(),
            'lesAnimaux' => $lesAnimaux,
        ]);
    }

    #[Route('/animal/demandermodification/{id<\d+>}', name: 'app_animal_demandermodification', methods: ['GET'])]
    public function demanderModification(Request $request, AnimalRepository $repository, PaginatorInterface $paginator, Animal $animalModif): Response
    {
        // créer l'objet et le formulaire de création
        $animal = new Animal();
        $formCreation = $this->createForm(AnimalType::class, $animal);

        // on  crée le formulaire de modification
        $formModificationView = $this->createForm(AnimalType::class, $animalModif)->createView();

        // lire les animaux
        $lesAnimaux = $paginator->paginate(
            $repository->findAll(),
            $request->query->getint('page', 1),
            5
        );
        return $this->render('animal/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesAnimaux' => $lesAnimaux,
            'formModification' => $formModificationView,
            'idAnimalModif' => $animalModif->getId(),
        ]);
    }

    #[Route('/animal/modifier/{id<\d+>}', name: 'app_animal_modifier', methods: ['POST'])]
    public function modifier(Animal $animal, PaginatorInterface $paginator, Request $request, EntityManagerInterface $entityManager, AnimalRepository $repository): Response
    // public function modifier(Animal $animal = null, $id = null, Request $request, EntityManagerInterface $entityManager, AnimalRepository $repository)
    {
        //  Symfony 4 est capable de retrouver l'animal à l'aide de Doctrine ORM directement en utilisant l'id passé dans la route
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // if ($form->isSubmitted() && $form->isValid()) {
            // va effectuer la requête d'UPDATE en base de données
            // pas besoin de "persister" l'entité car l'objet a déjà été retrouvé à partir de Doctrine ORM.
            $entityManager->flush();
            $this->addFlash(
                'success',
                'L\'animal ' . $animal->getNom() . ' a été modifiée.'
            );
            // rediriger vers l'affichage des animaux qui comprend le formulaire pour l"ajout d'un nouvel animal
            return $this->redirectToRoute('app_animal');
        } else {
            // affichage de la liste des animaux avec le formulaire de modification et ses erreurs
            // créer l'objet et le formulaire de création
            $animal = new Animal();
            $formCreation = $this->createForm(AnimalType::class, $animal);
            // lire les animaux
            $lesAnimaux = $paginator->paginate(
                $repository->findAll(),
                $request->query->getint('page', 1),
                5
            );
            // rendre la vue
            return $this->render('animal/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesAnimaux' => $lesAnimaux,
                'formModification' => $form->createView(),
                'idAnimalModif' => $animal->getId(),
            ]);
        }
    }

    #[Route('/animal/supprimer/{id<\d+>}', name: 'app_animal_supprimer', methods: ['GET'])]
    public function supprimer(Animal $animal, Request $request, EntityManagerInterface $entityManager)
    {
        // vérifier le token
        if ($this->isCsrfTokenValid('action-item' . $animal->getId(), $request->get('_token'))) {

            // supprimer l'animal
            $entityManager->remove($animal);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'L\'animal ' . $animal->getNom() . ' a été supprimé.'
            );
        }
        return $this->redirectToRoute('app_animal');
    }
}
