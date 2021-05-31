<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


	/**
     * @Route("/admin", name="admin_")
     */
class AdminController extends AbstractController
{

    /**
    * Lister les utilisateurs/employés
    * @Route("/utilisateurs", name="utilisateurs")
    */
    public function userList(UserRepository $users) {
        
        
    	return $this->render('admin/users.html.twig', [
    		'users' => $users->findAll()
    	]);
    }
}
