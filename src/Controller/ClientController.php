<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ClientRecherche;
use App\Form\ClientRechercheType;   
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Knp\Component\Pager\PaginatorInterface;


final class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client', methods: ['GET'])]
    public function index(Request $request, ClientRepository $repository, SessionInterface $session,PaginatorInterface $paginator): Response
    {
        // créer l'objet et le formulaire de recherche
        $clientRecherche = new ClientRecherche();
        // form en GET : Symfony lira les paramètres depuis $request->query
        $formRecherche = $this->createForm(ClientRechercheType::class, $clientRecherche, [
            'method' => 'GET',
            // optionnel : désactiver CSRF pour formulaires GET 
            // 'csrf_protection' => false,
        ]);
        $formRecherche->handleRequest($request);
        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $clientRecherche = $formRecherche->getData();
            // mémoriser les critères de sélection dans une variable de session
            $session->set('ClientCriteres', $clientRecherche);
            // cherche les clients correspondant aux critères, triés par libellé
            // requête construite dynamiquement alors il est plus simple d'utiliser le querybuilder
            $lesClients = $paginator->paginate(
                $repository->findAllByCriteria($clientRecherche),
                $request->query->getint('page', 1),
                5
            );
        } else {
            // lire les clients
            if ($session->has("ClientCriteres")) {
                // récupérer les critères en session
                $clientRecherche = $session->get("ClientCriteres");
                $lesClients = $paginator->paginate(
                    $repository->findAllByCriteria($clientRecherche),
                    $request->query->getint('page', 1),
                    5
                );
                $formRecherche = $this->createForm(ClientRechercheType::class, $clientRecherche);
                // injecter les critères en session dans le formulaire de recherche
                $formRecherche->setData($clientRecherche);
            } else {
                $prodRech = new ClientRecherche();
                $lesClients = $paginator->paginate(
                    $repository->findAllOrderByLibelle($prodRech),
                    $request->query->getint('page', 1),
                    5
                );
            }
        }
        $client = new Client();
        $formCreation = $this->createForm(ClientType::class, $client);

        return $this->render('client/index.html.twig', [
            'formRecherche' => $formRecherche,
            'lesClients' => $lesClients,
            'formCreation' => $formCreation->createView(),
            'idClientModif' => null,
            'formModification' => null,
        ]);
    }

    #[Route('/client/reinitialiser', name: 'app_client_reinitialiser', methods: ['GET'])]
    public function reinitialiser(Request $request): Response
    {
        // supprimer les critères de recherche en session
        $session = $request->getSession();
        if ($session->has('ClientsCriteres')) {
            $session->remove('ClientsCriteres');
        }

        return $this->redirectToRoute('app_client');
    }
    
    
    #[Route('/client/ajouter', name: 'app_client_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, ClientRepository $repository): Response

    {
        //  $Client objet de la classe Client, il contiendra les valeurs saisies dans les champs après soumission du formulaire.
        //  $request  objet avec les informations de la requête HTTP (GET, POST, ...)
        //  $entityManager  pour la persistance des données

        // création d'un formulaire de type ClientType
        $Client = new Client();
        $form = $this->createForm(ClientType::class, $Client);

        // handleRequest met à jour le formulaire
        //  si le formulaire a été soumis, handleRequest renseigne les propriétés
        //      avec les données saisies par l'utilisateur et retournées par la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // c'est le cas du retour du formulaire
            //         l'objet $Client a été automatiquement "hydraté" par Doctrine
            // dire à Doctrine que l'objet sera (éventuellement) persisté
            $entityManager->persist($Client);
            // exécuter les requêtes (indiquées avec persist) ici il s'agit de l'ordre INSERT qui sera exécuté
            $entityManager->flush();
            // ajouter un message flash de succès pour informer l'utilisateur
            $this->addFlash(
                'success',
                'Le client' . $Client->getNom() . ' a été ajoutée.'
            );
            // rediriger vers l'affichage des clients qui comprend le formulaire pour l"ajout d'une nouvelle client
            return $this->redirectToRoute('app_client');
        } else {
            // affichage de la liste des clients avec le formulaire de création et ses erreurs
            // lire les clients
            $lesClients = $repository->findAll();
            // rendre la vue
            return $this->render('client/index.html.twig', [
                'formCreation' => $form->createView(),
                'lesClients' => $lesClients,
                'formModification' => null,
                'idClientModif' => null,
            ]);
        }
    }
    #[Route('/Client/demandermodification/{id<\d+>}', name: 'app_client_demandermodification', methods: ['GET'])]
    public function demanderModification(ClientRepository $repository, Client $ClientModif, Request $request): Response
    {
        if ($this->isCsrfTokenValid('action-item' . $ClientModif->getId(), $request->get('_token'))) {
        // créer l'objet et le formulaire de création
        $Client = new Client();
        $formCreation = $this->createForm(ClientType::class, $Client);

        // on  crée le formulaire de modification
        $formModificationView = $this->createForm(ClientType::class, $ClientModif)->createView();

        // lire les clients
        $lesClients = $repository->findAll();
        return $this->render('client/index.html.twig', [
            'formCreation' => $formCreation->createView(),
            'lesClients' => $lesClients,
            'formModification' => $formModificationView,
            'idClientModif' => $ClientModif->getId(),
        ]);
    }
        return $this->redirectToRoute('app_client');
    }

    #[Route('/client/modifier/{id<\d+>}', name: 'app_client_modifier', methods: ['POST'])]
    public function modifier(Client $Client, Request $request, EntityManagerInterface $entityManager, ClientRepository $repository): Response
    // public function modifier(Client $Client = null, $id = null, Request $request, EntityManagerInterface $entityManager, ClientRepository $repository)
    {
        //  Symfony 4 est capable de retrouver la client à l'aide de Doctrine ORM directement en utilisant l'id passé dans la route
        $form = $this->createForm(ClientType::class, $Client);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // if ($form->isSubmitted() && $form->isValid()) {
            // va effectuer la requête d'UPDATE en base de données
            // pas besoin de "persister" l'entité car l'objet a déjà été retrouvé à partir de Doctrine ORM.
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Le client ' . $Client->getNom() . ' a été modifiée.'
            );
            // rediriger vers l'affichage des clients qui comprend le formulaire pour l"ajout d'une nouvelle client
            return $this->redirectToRoute('app_client');
        } else {
            // affichage de la liste des clients avec le formulaire de modification et ses erreurs
            // créer l'objet et le formulaire de création
            $Client = new Client();
            $formCreation = $this->createForm(ClientType::class, $Client);
            // lire les clients
            $lesClients = $repository->findAll();
            // rendre la vue
            return $this->render('client/index.html.twig', [
                'formCreation' => $formCreation->createView(),
                'lesClients' => $lesClients,
                'formModification' => $form->createView(),
                'idClientModif' => $Client->getId(),
            ]);
        }
    }
        #[Route('/Client/supprimer/{id<\d+>}', name: 'app_client_supprimer', methods: ['GET'])]
    public function supprimer(Client $Client, Request $request, EntityManagerInterface $entityManager)
    {
            // supprimer la client
            $entityManager->remove($Client);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Le client' . $Client->getNom() . ' a été supprimée.'
            );
        
        return $this->redirectToRoute('app_client');
    }

}