<?php

namespace App\Controller;

use App\Entity\UserDoc;
use App\Form\UserDocType;
use App\Repository\UserDocRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DocRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Repository\UserRepository;

/**
 * @Route("/{_locale}/share")
 */
class UserDocController extends AbstractController
{
    /**
     * @Route("/manage", name="user_doc_manage", methods={"GET","POST"})
     */
    public function new(Request $request, DocRepository $docRepository, UserRepository $userRepository, UserDocRepository $userDocRepository): Response
    {
        $doc = $docRepository->find($request->query->get('id'));
        // Relations list
        $userDocs = $userDocRepository->findBy(array('doc'=>$doc->getId()));
        // Users list
        $users = $userRepository->findAll();
        $sharedWith = array();
        $notSharedWith = array();
        foreach($users as $user){
            if($user->getId() != $this->getUser()->getId()){
                $in = false;
                foreach ($userDocs as $userDoc){
                    if($user->getId() == $userDoc->getUser()){
                        $sharedWith[] = array('email'=>$user->getEmail(),'userDocId'=>$userDoc->getId());
                        $in = true;
                        break;
                    }
                }
                if(!$in){
                    $notSharedWith[] = $user;
                }
            }
        }
        $form = $this->createFormBuilder([])
        ->add('users', ChoiceType::class, array(
            'choices' => $notSharedWith,
            'choice_label' => function($user, $key, $value) {
                return strtoupper($user->getEmail());
            },
            'choice_attr' => function($user, $key, $value) {
                return ['class' => 'user_'.strtolower($user->getId())];
            },
            'multiple' => true,
            'expanded' => true
        ))->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($data['users'] as $user){
                $userDoc = new UserDoc();
                $userDoc->setDoc($doc->getId());
                $userDoc->setUser($user->getId());
                $entityManager->persist($userDoc);
            }
            $entityManager->flush();

            return $this->redirectToRoute('doc_shared');
        }

        return $this->render('user_doc/manage.html.twig', [
            'doc' => $doc,
            'sharedWith' => $sharedWith,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="user_doc_delete", methods={"GET","DELETE"})
     */
    public function delete(Request $request, UserDoc $userDoc): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userDoc->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userDoc);
            $entityManager->flush();
        }
        return $this->redirectToRoute('doc_shared');
    }
}
