<?php

namespace App\Controller;

use App\Entity\Conges;
use App\Entity\User;
use App\Repository\CongesRepository;
use App\Repository\PermissionRepository;
use App\Entity\Permission;
use App\Entity\Note;
use App\Repository\NoteRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use App\Form\EditUserType;
use App\Form\EditNoteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class CongesController extends AbstractController
{
	/**
	 * @var CongesRepository
	 */
	//private $repository;

	public function __construct(CongesRepository $repository) {
		$this->repository = $repository;
	}


    // Affichage de tous les conges
    /**
     * @Route("/" , name="accueil_show")
     */
    public function dashboard(Request $request, CongesRepository $congesrepo, PermissionRepository $permisrepo): Response {
        $em = $this->getDoctrine()->getManager();
        //Requête d'affichage de tous les conges
        $conges = $this->repository->findAll();
        $congeList = "SELECT date_demande, prenom, etat from conges, user where user.id = conges.users_id;";
        $listConge = $em->getConnection()->prepare($congeList);
        $listConge->execute();
        $conges = $listConge->fetchAll();

        //Requête d'affichage de tous les permissions

        $RAW_QUERY = "SELECT date_demande, state, prenom from permission, user where user.id = permission.users_id;";

        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $permissions = $statement->fetchAll();

        /*Nombre de congés en attente
        $nb_attente = "SELECT count(*) as nbAttente from conges where etat = 'En attente';";
        $attente = $em->getConnection()->prepare($nb_attente);
        $attente->execute();
        $att = $attente->fetchAll();

        //Nombbre de congés à valider
        $nb_Avalider = "SELECT count(*) as nbAvalider from conges where etat = 'A valider';";
        $avalider = $em->getConnection()->prepare($nb_Avalider);
        $avalider->execute();
        $aval = $avalider->fetchAll();

        //Nombre de congés validé
        $nb_valid = "SELECT count(*) as nbValid  from conges where etat = 'Validé';";
        $valid = $em->getConnection()->prepare($nb_valid);
        $valid->execute();
        $val = $valid->fetchAll();

        //Nombre de congés réfusé
        $nb_refus = "SELECT count(*) as nbRefus from conges where etat = 'Refusé';";
        $refus = $em->getConnection()->prepare($nb_refus);
        $refus->execute();
        $ref = $refus->fetchAll();*/
        // CHART JS CONGES
        //Regrouper les etats des conges par leur valeur et ensuite les compter
        $conn = $congesrepo->countByEtat();
        $etat=[];
        $couleur=[];
        $nombres=[];

        foreach($conn as $con){
            $etat[] = $con['etat'];
            $couleur[] = $con['couleur'];
            $nombres[] = $con['nombres']; //'etat' et 'nombre' se trouvent dans dans countByEtat dans CongesRepository
        }

        // CHART JS PERMISSIONS
        // On regroupe les state des permissions par leur valeur et ensuite on les compte
        $perm = $permisrepo->countByState();
        $state = ['state'];
        $color = ['color'];
        $number=['number'];

        foreach($perm as $per){
            $state[] = $per['state'];
            $color[] = $per['color'];
            $number[] = $per['number'];
        }

        return $this->render('personnel/accueil.html.twig', [
            'tab' => 'bord',
            'conges' => $conges,
            'permissions' => $permissions,

            'etat' => json_encode($etat),
            'couleur' => json_encode($couleur),
            'nombres' => json_encode($nombres),

            'state' => json_encode($state),
            'color' => json_encode($color),
            'number' => json_encode($number)
        ]);
    }


	// Route vers gestion de congés
    /**
     * @Route("/gest_conges", name="conges_show")
     */
    public function conges(): Response
    {
        // Empêcher les utilisateurs à accéder au page gestion congé
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        

        //Affichage par requête
        $em = $this->getDoctrine()->getManager();

        $requete = "SELECT conges.id, users_id, date_depart, date_retour,motif, username, nb_jours from conges, user where conges.users_id = user.id and etat = 'En attente';";

        $statement = $em->getConnection()->prepare($requete);
        $statement->execute();
        $conges = $statement->fetchAll();
    	return $this->render('personnel/gest_conges.htmL.twig', [
    		'conge' => 'conges',
            'conges' => $conges
    	]);
    }

    //Premier validation de congé (premier validation)
    /**
     * @Route("/gest_conges/validate/{id}", name="conges_validate")
     * @Method({"POST"})
     */
    public function AvaliderConge(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        //mise en place du requête de validation
        $toValid_query = "UPDATE `conges` SET `etat` = 'A valider' , `etat_badge` = '#007bff' WHERE `conges`.`id` = $id ;";
        $statement = $em->getConnection()->prepare($toValid_query);

        $statement->execute();

        return true;
    }
    


    // Annulation d'une demande de congé
    /**
     * @Route("/gest_conges/refuse/{id}" , name="conge_delete")
     * @Method({"POST"})
     */
    public function delete(Request $request, $id) {
    	$em = $this->getDoctrine()->getManager();

        //mise en place du requête d'annulation
        $query = "UPDATE `conges` SET `etat` = 'Refusé', `etat_badge` = '#dc3545' WHERE `conges`.`id` = $id ;";
    	$statement = $em->getConnection()->prepare($query);
        $statement->execute();

        return $this->redirectToRoute('conges_show');
    }

    // Action pour la deuxième validation
    /**
     * @Route("/gest_conges/validerTwo/{id}", name="valid_two")
     */
    public function changer(Request $request, $id) {
        $conge = new Conges();

        $conge = $this->getDoctrine()->getRepository(Conges::class)->find($id);

        $changer = $this->createFormBuilder($conge)
                     ->add('etat', TextType::class, array(
                        'required' => true,
                        'attr' => array('class' => 'form-control' , 'hidden' => true ,
                        'value' => 'Validé')
                     ))

                     ->add('save', SubmitType::class, [
                        
                        'label' => 'Soumettre'
                     ])
                     ->getForm();

        $changer->handleRequest($request);

        if($changer->isSubmitted() && $changer->isValid()) {
            

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('conges_show');
        }
        return $this->render('conges/conge_valid_final.html.twig', array(
            'changer' => $changer->createView()
        ));
    }

    /**
     * Génération d'une solde congé
     * @Route("/gest_conges/generate/{id}", name="solde_generate")
     */
    public function genererSolde($id) {
        $em = $this->getDoctrine()->getManager();

        //générer une solde pour l'user
        $generate_query = "INSERT INTO `soldes` (`id`, `user_id`, `initial`, `consomme`, `restant`) VALUES (NULL, $id , '30', '0', '30');";

        //affecter l'avoir_solde de l'user en oui
        $affec_query = "UPDATE `user` SET `avoir_solde` = 1 WHERE `user`.`id` = $id;";
        $statement = $em->getConnection()->prepare($generate_query);
        $stmt = $em->getConnection()->prepare($affec_query);
        $statement->execute();
        $stmt->execute();

        return $this->redirectToRoute('personnel_show');
    }


    // Affichange conge à valider
    /**
     * @Route("/conges/final/{etat}", name="con_show")
     * @Method({"POST"})
     */
    public function affichFinal($etat) {
        $conge = $this->getDoctrine()->getRepository(Conges::class)->afficherCongesAvalider($etat);

        return $this->render('conges/conge_valid_final.html.twig', [
            'conge' => $conge
        ]);
    }

    /**
     * Publication d'une note interne et affichage
     * @Route("/admin/note_interne", name="note_interne")
     */
    public function publierNote(Request $request, ObjectManager $manager) {
        $note = new Note();
        $formNote = $this->createFormBuilder($note)
                    ->add('titre', TextType::class, [
                            'label' => 'Titre du note',
                            'required' => true,
                    ])
                    ->add('contenu', TextareaType::class, [
                            'label' => 'Ecrrivez votre message...',
                            'required' => true,
                    ])
                    ->add('save', SubmitType::class, [
                            'label' => 'Publier',
                    ])
                    ->getForm();
        $formNote->handleRequest($request);
        if($formNote->isSubmitted() && $formNote->isValid()) {
            $note->setUser($this->getUser());
            $note->setPublicationDate(new \Datetime());

            $manager->persist($note);
            $manager->flush();

            return $this->redirectToRoute('note_interne');
        }

        $user = $this->getUser();
        $notes = $this->getDoctrine()->getRepository(Note::class)->findByUser($user);


        return $this->render('personnel/note_interne.html.twig', [
            'note' => 'notes',
            'formNote' => $formNote->createView(),
            'note' => $notes,
            'no' => 'no'
        ]);
    }

    /**
     * Editer une note
     * @Route("/admin/note_interne/edit/{id}")
     */
    public function editNote(Note $note, Request $request) {
        $formNote = $this->createForm(EditNoteType::class, $note);
        $formNote->handleRequest($request);

        if($formNote->isSubmitted() && $formNote->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush();

            //$this->addFlash('message', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('note_interne');
        }
        return $this->render('personnel/editer_note.html.twig', [
            'noteForm' => $formNote->createView()
        ]);
    }


    /**
     * Suppression d'une note interne
     * @Route("/admin/note_interne/delete/{id}")
     * @Method({"DELETE"})
     */
    public function removeNote(Request $request, $id) {
        $note = $this->getDoctrine()->getRepository(Note::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($note);
        $entityManager->flush();

        $response = new Response();
        $response->send();
        return $this->redirectToRoute('note_interne');
    }

    //********************** BACK END SUPER ADMIN* **********************/

    /**
     * Dashboard pour admin
     * @Route("/sup_admin/homepage", name="admin_dash")
     */
    public function showAccueil(CongesRepository $congesrepo, PermissionRepository $permisrepo) {
        

        //$conges = $this->repository->findAll();
        
        // CHART JS CONGES
        //Regrouper les etats des conges par leur valeur et ensuite les compter
        $conn = $congesrepo->countByEtat();
        $etat=[];
        $couleur=[];
        $nombres=[];

        foreach($conn as $con){
            $etat[] = $con['etat'];
            $couleur[] = $con['couleur'];
            $nombres[] = $con['nombres']; //'etat' et 'nombre' se trouvent dans dans countByEtat dans CongesRepository
        }

        // CHART JS PERMISSIONS
        // On regroupe les state des permissions par leur valeur et ensuite on les compte
        $perm = $permisrepo->countByState();
        $state = ['state'];
        $color = ['color'];
        $number=['number'];

        foreach($perm as $per){
            $state[] = $per['state'];
            $color[] = $per['color'];
            $number[] = $per['number'];
        }
        $em = $this->getDoctrine()->getManager();
        //Requête d'affichage de tous les congés
        $reqCong = "SELECT prenom, date_demande, etat FROM conges, user where user.id = conges.users_id;";
        $stmt = $em->getConnection()->prepare($reqCong);
        $stmt->execute();
        $conges = $stmt->fetchAll();


        //Requête d'affichage de tous les permissions

        $reqPerm = "SELECT date_demande, state, prenom from permission, user where user.id = permission.users_id;";

        $statement = $em->getConnection()->prepare($reqPerm);
        $statement->execute();

        $permissions = $statement->fetchAll();

        return $this->render('sup_admin/dashboard.html.twig', [
            'tab' => 'bord',
            'conges' => $conges,
            'permissions' => $permissions,

            'etat' => json_encode($etat),
            'couleur' => json_encode($couleur),
            'nombres' => json_encode($nombres),

            'state' => json_encode($state),
            'color' => json_encode($color),
            'number' => json_encode($number)
        ]);
    }

    /**
     * Gestion congés pour super admin
     * @Route("/sup_admin/conges", name="sup_conges")
     */
    public function superCongeAvalider() {
        $em = $this->getDoctrine()->getManager();
        $req = "SELECT conges.id, users_id, date_depart, date_retour,motif, username, nb_jours from conges, user where conges.users_id = user.id and etat = 'A valider';";
        $statement = $em->getConnection()->prepare($req);
        $statement->execute();
        $conges = $statement->fetchAll();
        return $this->render('/sup_admin/conges.html.twig', [
            'conge' => 'conges',
            'conges' => $conges,

        ]);
    }

    /**
     * Changement d'etat du congé en Validé
     * @Route("/sup_admin/valider/{id}")
     */
    public function validerConge($id) {
        $em = $this->getDoctrine()->getManager();

        //mise en place du requête de validation
        $toValid_query = "UPDATE `conges` SET `etat` = 'Validé', `etat_badge` = '#28a745' WHERE `conges`.`id` = $id ;";
        $statement = $em->getConnection()->prepare($toValid_query);

        $statement->execute();

        return $this->redirectToRoute('sup_conges');
    }


    /**
     * Mise à jour du solde (consomme)
     * @Route("/sup_admin/addconsom/{nb}/{id}", name="cumul_consomme")
     * @Method({"POST0"})
     */
    public function cumulConsomme($nb, $id) {
        $em = $this->getDoctrine()->getManager();

        $cumul_query = "UPDATE `soldes` SET `consomme` = `consomme` + $nb WHERE `soldes`.`user_id` = $id;";
        $statement = $em->getConnection()->prepare($cumul_query);
        $statement->execute();
        return $this->redirectToRoute('supp_conges');
    }

    /**
     * Mise à jour du solde (colonne restant)
     * @Route("/sup_admin/soustract/{nb}/{id}", name="soldes_soustract")
     * @Method({"POST"})
     */
    public function soustractSolde($nb, $id) {
        $em = $this->getDoctrine()->getManager();

        $toSoustract_query = "UPDATE `soldes` SET `restant` = `restant` - $nb WHERE `soldes`.`user_id` = $id;";
        $statement = $em->getConnection()->prepare($toSoustract_query);
        $statement->execute();
        return $this->redirectToRoute('sup_conges');
    }

    /**
     * Vers page permission
     * @Route("/sup_admin/permission", name="sup_permissions")
     */
    public function supPermission() {
        $em = $this->getDoctrine()->getManager();
        $req = "SELECT permission.id, username, date_permission, heure_depart, heure_retour, sujet FROM permission, user WHERE user.id = permission.users_id AND `state` = 'A valider';";
        $statement = $em->getConnection()->prepare($req);
        $statement->execute();
        $permissions = $statement->fetchAll();
        return $this->render('sup_admin/permission.html.twig', [
            'permission' => 'permissions',
            'permis' => $permissions
        ]);
    }

    /**
     * Valider une permission
     * @Route("/sup_admin/permisValider/{id}")
     */
    public function validerPermission($id) {
        $em = $this->getDoctrine()->getManager();
        $req = "UPDATE `permission` set `state` = 'Validé', `state_badge` = '#28a745' WHERE `permission`.`id` = $id;";
        $statement = $em->getConnection()->prepare($req);
        $statement->execute();
        return $this->redirectToRoute('sup_permissions');
    }

    /**
     * Refuser une permission
     * @Route("/sup_admin/permisRefuser/{id}")
     */
    public function refusPermission($id) {
        $em = $this->getDoctrine()->getManager();
        $req = "UPDATE `permission` set `state` = 'Refusé', `state_badge` = '#dc3545' WHERE `permission`.`id` = $id;";
        $statement = $em->getConnection()->prepare($req);
        $statement->execute();
        return $this->redirectToRoute('sup_permissions');
    }
    
    
    /**
     * Vers page personnel
     * @Route("/sup_admin/personnel", name="sup_personnel")
     */
    public function supPersonnel() {

        //$user = $this->getUser()->getId();
        $employes = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('sup_admin/personnel.html.twig', [
            'personnel' => 'personnels',
            'employes' => $employes
        ]);
    }

    /**
     * Editer un personnel
     * @Route("/sup_admin/editer/{id}", name="user_edit")
     */
    public function editPerso(User $user, Request $request) {
        $formUser = $this->createForm(EditUserType::class, $user);
        $formUser->handleRequest($request);

        if($formUser->isSubmitted() && $formUser->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('sup_personnel');
        }
        return $this->render('sup_admin/edit_user.html.twig', [
            'userForm' => $formUser->createView()
        ]);
    }

    /**
     * Suppprimer un personnel
     * @Route("/sup_admin/delete/{id}", name="user_delete")
     */
    public function supprimerPerso(Request $request, $id) {
        $employe = $this->getDoctrine()->getRepository(User::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($employe);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }

}
