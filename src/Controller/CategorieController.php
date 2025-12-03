<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Entity\Produit;
use App\Entity\ProduitRecherche;
use App\Repository\CategorieRepository;
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



final class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie', methods: ['GET'])]
    public function index(Request $request, CategorieRepository $repository, PaginatorInterface $paginator): Response
    {
        // créer l'objet et le formulaire de création
        $categorie = new Categorie();
        $formCreation = $this->createForm(CategorieType::class, $categorie);

        // lire les catégories
        $lesCategories = $paginator->paginate(
            $repository->findAll(),
            $request->query->getint('page', 1),
            5
        );
        return $this->render('categorie/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesCategories' => $lesCategories,
            'idCategorieModif' => null,
            'formModification' => null,
        ]);
    }


    #[Route('/categorie/ajouter', name: 'app_categorie_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager, CategorieRepository $repository): Response

    {
        //  $categorie objet de la classe Categorie, il contiendra les valeurs saisies dans les champs après soumission du formulaire.
        //  $request  objet avec les informations de la requête HTTP (GET, POST, ...)
        //  $entityManager  pour la persistance des données

        // création d'un formulaire de type CategorieType
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);

        // handleRequest met à jour le formulaire
        //  si le formulaire a été soumis, handleRequest renseigne les propriétés
        //      avec les données saisies par l'utilisateur et retournées par la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // c'est le cas du retour du formulaire
            //         l'objet $categorie a été automatiquement "hydraté" par Doctrine
            // dire à Doctrine que l'objet sera (éventuellement) persisté
            $entityManager->persist($categorie);
            // exécuter les requêtes (indiquées avec persist) ici il s'agit de l'ordre INSERT qui sera exécuté
            $entityManager->flush();
            // ajouter un message flash de succès pour informer l'utilisateur
            $this->addFlash(
                'success',
                'La catégorie ' . $categorie->getLibelle() . ' a été ajoutée.'
            );
            // rediriger vers l'affichage des catégories qui comprend le formulaire pour l"ajout d'une nouvelle catégorie
            return $this->redirectToRoute('app_categorie');
        } else {
            // affichage de la liste des catégories avec le formulaire de création et ses erreurs
            // lire les catégories
            $lesCategories = $paginator->paginate(
                $repository->findAll(),
                $request->query->getint('page', 1),
                5
            );
            // rendre la vue
            return $this->render('categorie/index.html.twig', [
                'formCreation' => $form->createView(),
                'lesCategories' => $lesCategories,
                'formModification' => null,
                'idCategorieModif' => null,
            ]);
        }
    }

    #[Route('/categorie/demandermodification/{id<\d+>}', name: 'app_categorie_demandermodification', methods: ['GET'])]
    public function demanderModification(CategorieRepository $repository, PaginatorInterface $paginator, Categorie $categorieModif, Request $request): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $categorieModif->getId(), $request->get('_token'))) {
            // créer l'objet et le formulaire de création
            $categorie = new Categorie();
            $formCreation = $this->createForm(CategorieType::class, $categorie);

            // on  crée le formulaire de modification
            $formModificationView = $this->createForm(CategorieType::class, $categorieModif)->createView();

            // lire les catégories
            // Pas de changement majeur nécessaire ici, juste pour confirmer :
            $lesCategories = $paginator->paginate(
                $repository->findAll(),
                $request->query->getInt('page', 1), // Cela capture bien la page envoyée par le bouton Twig
                5
            );
            return $this->render('categorie/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesCategories' => $lesCategories,
                'formModification' => $formModificationView,
                'idCategorieModif' => $categorieModif->getId(),
            ]);
        }
        return $this->redirectToRoute('app_categorie');
    }

    #[Route('/categorie/modifier/{id<\d+>}', name: 'app_categorie_modifier', methods: ['POST'])]
    public function modifier(
        Categorie $categorie,
        SluggerInterface $slugger,
        Request $request,
        EntityManagerInterface $entityManager,
        CategorieRepository $repository,
        PaginatorInterface $paginator // AJOUT : Nécessaire pour le cas d'erreur
    ): Response {
        // 1. On récupère le numéro de page actuel (ou 1 par défaut)
        $page = $request->query->getInt('page', 1);

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { // J'ai remis le isValid() c'est plus sûr

            $entityManager->flush();
            $this->addFlash(
                'success',
                'La catégorie ' . $categorie->getLibelle() . ' a été modifiée.'
            );

            // 2. MODIFICATION ICI : On redirige en gardant le paramètre page
            return $this->redirectToRoute('app_categorie', ['page' => $page]);
        } else {
            // Cas d'erreur : on doit réafficher la page actuelle avec les erreurs

            $categorieNew = new Categorie();
            $formCreation = $this->createForm(CategorieType::class, $categorieNew);

            // 3. CORRECTION ICI : On utilise le paginator comme dans index()
            // Au lieu de $repository->findAll() tout seul qui casse l'affichage
            $lesCategories = $paginator->paginate(
                $repository->findAll(),
                $page, // On reste sur la page actuelle
                5
            );

            return $this->render('categorie/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesCategories' => $lesCategories,
                'formModification' => $form->createView(),
                'idCategorieModif' => $categorie->getId(),
            ]);
        }
    }


    #[Route('/categorie/supprimer/{id<\d+>}', name: 'app_categorie_supprimer')]
    public function supprimer(Categorie $categorie, Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. On récupère le numéro de page
        $page = $request->query->getInt('page', 1);

        // vérifier le token
        if ($this->isCsrfTokenValid('action-item' . $categorie->getId(), $request->get('_token'))) {
            if ($categorie->getProduits()->count() > 0) {
                $this->addFlash(
                    'error',
                    'Il existe des produits dans la catégorie ' . $categorie->getLibelle() . ', elle ne peut pas être supprimée.'
                );
                // On redirige avec la page
                return $this->redirectToRoute('app_categorie', ['page' => $page]);
            }
            // supprimer la catégorie
            $entityManager->remove($categorie);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La catégorie ' . $categorie->getLibelle() . ' a été supprimée.'
            );
        }

        // 2. MODIFICATION ICI : redirection vers la bonne page
        return $this->redirectToRoute('app_categorie', ['page' => $page]);
    }

    #[Route('/categorie/statistique', name: 'app_categorie_statistique')]
    public function statistique(CategorieRepository $repository, ProduitRepository $repositoryProduit): Response
    {
        $lesCategories = $repository->findAll();
        $tbCategoriesDesc = [];

        foreach ($lesCategories as $uneCategorie) {
            $produitRecherche = new ProduitRecherche();
            $produitRecherche->setCategorie($uneCategorie);

            // On garde l'hydratation en tableau
            $produitResultat = $repositoryProduit->findAllByCriteria($produitRecherche)
                ->execute(array(), Query::HYDRATE_ARRAY);

            $nbProduits = count($produitResultat);

            // GESTION DU CAS : Catégorie vide (0 produit)
            if ($nbProduits === 0) {
                $tbCategoriesDesc[] = [
                    "name" => $uneCategorie->getLibelle(),
                    "nbProduits" => 0,
                    "prixMin" => 0,
                    "prixMax" => 0,
                    "prixMoyen" => 0
                ];
                continue; // On passe à la catégorie suivante
            }

            // Initialisation avec les valeurs du premier produit (syntaxe tableau !)
            $prixMin = $produitResultat[0]['prix'];
            $prixMax = $produitResultat[0]['prix'];
            $sommePrix = 0;

            foreach ($produitResultat as $unProduit) {
                // CORRECTION ICI : Utilisation des crochets [] au lieu de ->getPrix()
                $prixActuel = $unProduit['prix'];

                if ($prixActuel < $prixMin) {
                    $prixMin = $prixActuel;
                }
                if ($prixActuel > $prixMax) {
                    $prixMax = $prixActuel;
                }
                $sommePrix += $prixActuel;
            }

            $unTabCateg = [
                "name" => $uneCategorie->getLibelle(),
                "nbProduits" => $nbProduits,
                "prixMin" => $prixMin,
                "prixMax" => $prixMax,
                "prixMoyen" => round($sommePrix / $nbProduits, 2) // Plus de risque de division par zéro grâce au if plus haut
            ];

            $tbCategoriesDesc[] = $unTabCateg;
        }

        return $this->render('categorie/statistique.html.twig', [
            'tbCategorieDesc' => $tbCategoriesDesc
        ]);
    }
}
