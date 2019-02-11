<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\DocRepository;

/**
 * @Route("/{_locale}/user")
 */
class UserController extends AbstractController
{
    
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoderInterface $passwordEncoder){
        $this->passwordEncoder = $passwordEncoder;
    }
    
    

    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $isAdmin = false;
        $targetRoute = 'app_login';
        if($this->getUser() != null){
            if(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
                $isAdmin = true;
                $targetRoute = 'user_index';
            }
        }
        // If current user is an admin, he can give admin right to another profile
        $form = $this->createForm(UserType::class, $user, array(
            'attr' => ['isAdmin'=>$isAdmin] 
            ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute($targetRoute);
        }
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $isAdmin = false;
        $targetRoute = 'app_account';
        if($this->getUser() != null){
            if(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
                $isAdmin = true;
                $targetRoute = 'user_index';
            }
        }
        // If current user is an admin, he can give admin right to another profile
        $form = $this->createForm(UserType::class, $user, array(
            'attr' => ['isAdmin'=>$isAdmin]
        ));
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute($targetRoute);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
    
//     /**
//      * @IsGranted("ROLE_USER")
//      * @Route("/{id}/doc/list", name="doc_list", methods={"GET"})
//      */
//     public function index(DocRepository $docRepository): Response
//     {
//         return $this->render('doc/index.html.twig', [
//             'docs' => $docRepository->findBy(array('user')),
//         ]);
//     }
}
