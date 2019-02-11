<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\DocRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\UserRepository;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/{_locale}/admin")
 */
class AdminController extends AbstractController
{
    /**
     * 
     * @Route("/", name="app_admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    
    /**
     * @Route("/users", name="user_index", methods={"GET"})
     */
    public function userIndex(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    
    /**
     * @Route("/docs", name="doc_index", methods={"GET"})
     */
    public function docIndex(DocRepository $docRepository): Response
    {
        return $this->render('doc/index.html.twig', [
            'docs' => $docRepository->findAll(),
        ]);
    }
}
