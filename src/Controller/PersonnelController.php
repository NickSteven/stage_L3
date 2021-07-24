<?php

namespace App\Controller;


use App\Entity\Conges;
use App\Entity\User;
use App\Entity\Permission;
use App\Entity\UserRepository;
use App\Repository\PermissionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\EditUserType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PersonnelController extends AbstractController
{	


    // Route vers gestion personnel
    /**
     * @Route("/admin/gest_personnel", name="personnel_show")
     */
    public function personnel() {

    	$employes = $this->getDoctrine()->getRepository(User::class)->findAll();
    	
    	return $this->render('personnel/gest_personnels.html.twig',[
    		'personnel' => 'personnels',
    		'employes' => $employes
    	]);
    }


    // Editer un employé
    /**
     * @Route("/gest_personnel/edit/{id}", name="edit_employe")
     */
    public function editEmploye(User $user, Request $request) {
        $formUser = $this->createForm(EditUserType::class, $user);
        $formUser->handleRequest($request);

        if($formUser->isSubmitted() && $formUser->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('personnel_show');
        }
        return $this->render('personnel/edit_user.html.twig', [
            'userForm' => $formUser->createView()
        ]);
        
    }

    // Suppression d'un employé
    /**
     * @Route("/gest_personnel/delete/{id}", name="supp_employe")
     * @Method({"DELETE"})
    */
    public function delete(Request $request, $id) {
        $employe = $this->getDoctrine()->getRepository(User::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($employe);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }

    // Route vers gestion permission
    /**
     * @Route ("/admin/gest_permission", name="permission_show")
     */
    public function permissionList() {
        $em = $this->getDoctrine()->getManager();

        //Requête pour afficher les permissions en attente
        $req = "SELECT permission.id, username, date_permission, heure_depart, heure_retour, sujet from user, permission where user.id = permission.users_id and state = 'En attente';";
        $stmt = $em->getConnection()->prepare($req);
        $stmt->execute();
        $permission = $stmt->fetchAll();


    	return $this->render('personnel/gest_permission.html.twig', [
    		'permission' => 'permissions',
            'permis' => $permission
    	]);
    }

    /**
     * Validation permission (premier validation)
     * @Route("/gest_permission/valider/{id}", name="permission_vaider")
     * @Method({"POST"})
     */
    public function validerPermission(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        // Requête de validation
        $req = "UPDATE `permission` SET `state` = 'A valider', `state_badge` = '#007bff' WHERE `permission`.`id` = $id ;";
        $stmt = $em->getConnection()->prepare($req);
        $stmt->execute();

        return true;
    }

    /**
     * Refus d'une permission
     * @Route("/gest_permission/refuser/{id}", name="permission_refuser")
     * @Method({"POST"})
     */
    public function refuserPermission($id) {
        $em = $this->getDoctrine()->getManager();

        // Requête pour le refus
        $req = "UPDATE `permission` SET `state` = 'Refusé', `state_badge` = '#dc3545' WHERE `permission`.`id` = $id;";
        $stmt = $em->getConnection()->prepare($req);
        $stmt->execute();

        return true;
    }


    // Voir détail enployé
    /**
     * @Route ("/gest_personnel/{id}", name="employe_show")
    */
    public function show($id) {
    	$employe = $this->getDoctrine()->getRepository(Employe::class)->find($id);

    	return $this->render('personnel/affich_perso.html.twig', 
    		array('employe' => $employe));
    }

    // Demande congé
    

    

}
