<?php

namespace App\Controller;

use App\Entity\Conditionnement;
use App\Form\ConditionnementType;
use App\Entity\Produit;
use App\Entity\ProduitRecherche;
use App\Repository\ConditionnementRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;



final class ConditionnementController extends AbstractController
{
    #[Route('/conditionnement', name: 'app_conditionnement', methods: ['GET'])]
    public function index(Request $request, ConditionnementRepository $repository, PaginatorInterface $paginator): Response
    {
        // créer l'objet et le formulaire de création
        $conditionnement = new Conditionnement();
        $formCreation = $this->createForm(ConditionnementType::class, $conditionnement);

        // lire les catégories
        $lesConditionnements = $paginator->paginate(
            $repository->findAll(),
            $request->query->getint('page', 1),
            5
        );
        return $this->render('conditionnement/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesConditionnements' => $lesConditionnements,
            'idConditionnementModif' => null,
            'formModification' => null,
        ]);
    }


    #[Route('/conditionnement/ajouter', name: 'app_conditionnement_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager, ConditionnementRepository $repository): Response

    {
        //  $conditionnement objet de la classe Conditionnement, il contiendra les valeurs saisies dans les champs après soumission du formulaire.
        //  $request  objet avec les informations de la requête HTTP (GET, POST, ...)
        //  $entityManager  pour la persistance des données

        // création d'un formulaire de type ConditionnementType
        $conditionnement = new Conditionnement();
        $form = $this->createForm(ConditionnementType::class, $conditionnement);

        // handleRequest met à jour le formulaire
        //  si le formulaire a été soumis, handleRequest renseigne les propriétés
        //      avec les données saisies par l'utilisateur et retournées par la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // c'est le cas du retour du formulaire
            //         l'objet $conditionnement a été automatiquement "hydraté" par Doctrine
            // dire à Doctrine que l'objet sera (éventuellement) persisté
            $entityManager->persist($conditionnement);
            // exécuter les requêtes (indiquées avec persist) ici il s'agit de l'ordre INSERT qui sera exécuté
            $entityManager->flush();
            // ajouter un message flash de succès pour informer l'utilisateur
            $this->addFlash(
                'success',
                'La catégorie ' . $conditionnement->getLibelle() . ' a été ajoutée.'
            );
            // rediriger vers l'affichage des catégories qui comprend le formulaire pour l"ajout d'une nouvelle catégorie
            return $this->redirectToRoute('app_conditionnement');
        } else {
            // affichage de la liste des catégories avec le formulaire de création et ses erreurs
            // lire les catégories
            $lesConditionnements = $paginator->paginate(
                $repository->findAll(),
                $request->query->getint('page', 1),
                5
            );
            // rendre la vue
            return $this->render('conditionnement/index.html.twig', [
                'formCreation' => $form->createView(),
                'lesConditionnements' => $lesConditionnements,
                'formModification' => null,
                'idConditionnementModif' => null,
            ]);
        }
    }

    #[Route('/conditionnement/demandermodification/{id<\d+>}', name: 'app_conditionnement_demandermodification', methods: ['GET'])]
    public function demanderModification(ConditionnementRepository $repository, PaginatorInterface $paginator, Conditionnement $conditionnementModif, Request $request): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $conditionnementModif->getId(), $request->get('_token'))) {
            // créer l'objet et le formulaire de création
            $conditionnement = new Conditionnement();
            $formCreation = $this->createForm(ConditionnementType::class, $conditionnement);

            // on  crée le formulaire de modification
            $formModificationView = $this->createForm(ConditionnementType::class, $conditionnementModif)->createView();

            // lire les catégories
            // Pas de changement majeur nécessaire ici, juste pour confirmer :
            $lesConditionnements = $paginator->paginate(
                $repository->findAll(),
                $request->query->getInt('page', 1), // Cela capture bien la page envoyée par le bouton Twig
                5
            );
            return $this->render('conditionnement/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesConditionnements' => $lesConditionnements,
                'formModification' => $formModificationView,
                'idConditionnementModif' => $conditionnementModif->getId(),
            ]);
        }
        return $this->redirectToRoute('app_conditionnement');
    }

    #[Route('/conditionnement/modifier/{id<\d+>}', name: 'app_conditionnement_modifier', methods: ['POST'])]
    public function modifier(
        Conditionnement $conditionnement,
        SluggerInterface $slugger,
        Request $request,
        EntityManagerInterface $entityManager,
        ConditionnementRepository $repository,
        PaginatorInterface $paginator // AJOUT : Nécessaire pour le cas d'erreur
    ): Response {
        // 1. On récupère le numéro de page actuel (ou 1 par défaut)
        $page = $request->query->getInt('page', 1);

        $form = $this->createForm(ConditionnementType::class, $conditionnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { // J'ai remis le isValid() c'est plus sûr

            $entityManager->flush();
            $this->addFlash(
                'success',
                'La catégorie ' . $conditionnement->getLibelle() . ' a été modifiée.'
            );

            // 2. MODIFICATION ICI : On redirige en gardant le paramètre page
            return $this->redirectToRoute('app_conditionnement', ['page' => $page]);
        } else {
            // Cas d'erreur : on doit réafficher la page actuelle avec les erreurs

            $conditionnementNew = new Conditionnement();
            $formCreation = $this->createForm(ConditionnementType::class, $conditionnementNew);

            // 3. CORRECTION ICI : On utilise le paginator comme dans index()
            // Au lieu de $repository->findAll() tout seul qui casse l'affichage
            $lesConditionnements = $paginator->paginate(
                $repository->findAll(),
                $page, // On reste sur la page actuelle
                5
            );

            return $this->render('conditionnement/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesConditionnements' => $lesConditionnements,
                'formModification' => $form->createView(),
                'idConditionnementModif' => $conditionnement->getId(),
            ]);
        }
    }


    #[Route('/conditionnement/supprimer/{id<\d+>}', name: 'app_conditionnement_supprimer')]
    public function supprimer(Conditionnement $conditionnement, Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. On récupère le numéro de page
        $page = $request->query->getInt('page', 1);

        // vérifier le token
        if ($this->isCsrfTokenValid('action-item' . $conditionnement->getId(), $request->get('_token'))) {
            if ($conditionnement->getLesProduits()->count() > 0) {
                $this->addFlash(
                    'error',
                    'Il existe des produits dans le conditionnement ' . $conditionnement->getLibelle() . ', elle ne peut pas être supprimée.'
                );
                // On redirige avec la page
                return $this->redirectToRoute('app_conditionnement', ['page' => $page]);
            }
            // supprimer la catégorie
            $entityManager->remove($conditionnement);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La catégorie ' . $conditionnement->getLibelle() . ' a été supprimée.'
            );
        }

        // 2. MODIFICATION ICI : redirection vers la bonne page
        return $this->redirectToRoute('app_conditionnement', ['page' => $page]);
    }
}
