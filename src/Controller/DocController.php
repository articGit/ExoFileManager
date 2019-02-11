<?php

namespace App\Controller;

use App\Entity\Doc;
use App\Form\DocType;
use App\Repository\DocRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Repository\UserRepository;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\UserType;
use App\Repository\UserDocRepository;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/{_locale}/doc")
 */
class DocController extends BaseController
{
    /**
     * @Route("/list", name="doc_list", methods={"GET"})
     */
    public function list(DocRepository $docRepository): Response
    {
        return $this->render('doc/list.html.twig', [
            'docs' => $docRepository->findBy(array('owner'=>$this->getUser())),
        ]);
    }
    
    /**
     * @Route("/shared", name="doc_shared", methods={"GET"})
     */
    public function shared(DocRepository $docRepository, UserDocRepository $userDocRepository): Response
    {
        $docsOwnByUser = $docRepository->findBy(array('owner'=>$this->getUser()->getId()));
        $userDocs = array();
        foreach($docsOwnByUser as $d){
            $temp = $userDocRepository->findOneBy(array('doc'=>$d->getId()));
            if($temp != null){
                $userDocs[] = $temp;
            }
        }
        $docs = [];
        foreach ($userDocs as $userDoc){
            $sharedDoc['doc'] = $docRepository->findBy(array('id' => $userDoc->getDoc()))[0];
            $sharedDoc['userDocId'] = $userDoc->getId();
            $docs[] = $sharedDoc;
        }
        return $this->render('doc/list_shared.html.twig', [
            'docs' => $docs
        ]);
    }
    
    /**
     * @Route("/new", name="doc_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $doc = new Doc();
        $doc->setOwner($this->getUser());
        $form = $this->createForm(DocType::class, $doc);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $doc->getFilename();
            $name = explode(".", $file->getClientOriginalName())[0];
            $fileName = $name.'.'.$file->guessExtension();
            try {
                $file->move($this->getParameter('files_directory'), $fileName);
            } catch (FileException $e) {
                return new Response($e->getMessage());
            }
            $doc->setFilename($fileName);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($doc);
            $entityManager->flush();
            return $this->redirectToRoute('doc_list');
        }
        return $this->render('doc/new.html.twig', [
            'doc' => $doc,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="doc_show", methods={"GET"})
     */
    public function show(Doc $doc): Response
    {
        return $this->render('doc/show.html.twig', [
            'doc' => $doc,
        ]);
    }

    /**
     * @Route("/{id}", name="doc_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Doc $doc): Response
    {
        if ($this->isCsrfTokenValid('delete'.$doc->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($doc);
            $entityManager->flush();
        }
        return $this->redirectToRoute('doc_list');
    }
}
