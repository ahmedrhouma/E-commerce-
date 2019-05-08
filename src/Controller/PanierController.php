<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\CategorieRepository;
use App\Repository\ArticleRepository;


/**
 * @Route("/panier")
 */
class PanierController extends AbstractController
{
    /**
     * @Route("/", name="panier_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository,Request $request,PanierRepository $panierRepository,CategorieRepository $CategorieRepository,Security $security): Response
    {  



        $user = $security->getUser();
        $nbpanier=count($panierRepository->findBy(['User' => $user ]));
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findBy(['User' => $user ]),
            'articles' => $articleRepository->findAll(),
            'categories' => $CategorieRepository->findAll(),
            'nbpanier' => $nbpanier
    
        ]);
    }

    /**
     * @Route("/{id}", name="panier_delete", methods={"DELETE"})
     */
    public function delete(Request $request,Panier $panier): Response
    {

        if ($this->isCsrfTokenValid('delete'.$panier->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($panier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('panier_index');
    }
}
