<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Entity\Produit;
use App\Entity\ProduitRecherche;
use App\Entity\CategorieRecherche;
use App\Form\CategorieRechercheType;
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

        // créer l'objet et le formulaire de recherche
        $categorieRecherche = new CategorieRecherche();
        // form en GET : Symfony lira les paramètres depuis $request->query
        $formRecherche = $this->createForm(CategorieRechercheType::class, $categorieRecherche, [
            'method' => 'GET',
            // optionnel : désactiver CSRF pour formulaires GET 
            // 'csrf_protection' => false,
        ]);
        $formRecherche->handleRequest($request);
        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $categorieRecherche = $formRecherche->getData();
            // cherche les produits correspondant aux critères, triés par libellé
            // requête construite dynamiquement alors il est plus simple d'utiliser le querybuilder
            $lesCategories = $repository->findAllByCriteria($categorieRecherche);
        } else {
            $lesCategories = $repository->findAllOrderByLibelle();
        }


        // lire les catégories
        $lesCategories = $paginator->paginate(
            $lesCategories,
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
    public function ajouter(Request $request, EntityManagerInterface $entityManager, CategorieRepository $repository, PaginatorInterface $paginator): Response
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
        if ($form->isSubmitted() && $form->isValid()) {
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
        }
        // rediriger vers l'URL de retour (liste avec page et filtres)  
        return $this->redirectToRoute('app_categorie', $request->query->all());
    }


    #[Route('/categorie/demandermodification/{id<\d+>}', name: 'app_categorie_demandermodification', methods: ['GET'])]
    public function demanderModification(Categorie $categorieModif, CategorieRepository $repository, Request $request, PaginatorInterface $paginator): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $categorieModif->getId(), $request->get('_token'))) {
            // créer l'objet et le formulaire de création
            $categorie = new Categorie();
            $formCreation = $this->createForm(CategorieType::class, $categorie);

            // on  crée le formulaire de modification
            $formModificationView = $this->createForm(CategorieType::class, $categorieModif)->createView();

            // créer l'objet et le formulaire de recherche
            $categorieRecherche = new CategorieRecherche();
            // form en GET : Symfony lira les paramètres depuis $request->query
            $formRecherche = $this->createForm(CategorieRechercheType::class, $categorieRecherche, [
                'method' => 'GET',
                // optionnel : désactiver CSRF pour formulaires GET --> plus propre car Symfony ne fait pas la vérification automatique pour les GET 
                // 'csrf_protection' => false,
            ]);
            $formRecherche->handleRequest($request);
            if ($formRecherche->isSubmitted()) {
                $categorieRecherche = $formRecherche->getData();
                // cherche les produits correspondant aux critères, triés par libellé
                // requête construite dynamiquement alors il est plus simple d'utiliser le querybuilder
                $lesCategories = $repository->findAllByCriteria($categorieRecherche);
            } else {
                $lesCategories = $repository->findAllOrderByLibelle();
            }

            // paginer les catégories
            $lesCategories = $paginator->paginate(
                $lesCategories,
                $request->query->getInt('page', 1), // Cela capture bien la page envoyée par le bouton Twig
                5
            );
            return $this->render('categorie/index.html.twig', [
                'formRecherche' => $formRecherche,
                'formCreation' => $formCreation->createView(),
                'lesCategories' => $lesCategories,
                'formModification' => $formModificationView,
                'idCategorieModif' => $categorieModif->getId(),
            ]);
        }
       // on ajoute la query de retour (page, filtres) pour éviter de perdre le contexte de la liste après l'action
        return $this->redirectToRoute('app_categorie', $request->query->all());
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


    #[Route('/categorie/supprimer/{id<\d+>}', name: 'app_categorie_supprimer', methods: ['GET'])]
    public function supprimer(Categorie $categorie, Request $request, EntityManagerInterface $entityManager)
    {
        // vérifier le token
        if ($this->isCsrfTokenValid('action-item' . $categorie->getId(), $request->get('_token'))) {
            if ($categorie->getProduits()->count() > 0) {
                $this->addFlash(
                    'error',
                    'Il existe des produits dans la catégorie ' . $categorie->getLibelle() . ', elle ne peut pas être supprimée.'
                );
                return $this->redirectToRoute('app_categorie', $request->query->all());
            }
            // supprimer la catégorie
            $entityManager->remove($categorie);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La catégorie ' . $categorie->getLibelle() . ' a été supprimée.'
            );
        }
        // rediriger vers l'URL de retour (liste avec page et filtres)
        return $this->redirectToRoute('app_categorie', $request->query->all());
    }

    #[Route('/categorie/statproduits', name: 'app_categorie_statproduits', methods: ['GET'])]
    public function statproduits(CategorieRepository $repository): Response
    {
        return $this->render('categorie/statproduits.html.twig', [
            'lesCategoriesStats' => $repository->findAllWithStats()
        ]);
    }

}
