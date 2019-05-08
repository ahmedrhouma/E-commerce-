<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commande;
use App\Entity\Panier;
use App\Entity\Categorie;
use App\Form\ArticleType;
use App\Form\CommandeType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\Security;
use App\Repository\PanierRepository;
use App\Repository\CategorieRepository;
use App\Repository\ArticleRepository;


/**
 * @Route("/")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/article", name="article_index", methods={"GET","POST"})
     */
    public function index(ArticleRepository $articleRepository,Request $request,PanierRepository $panierRepository,CategorieRepository $CategorieRepository,Security $security): Response
    {
        $em = $this->getDoctrine()->getManager();
     $repo = $em->getRepository(Article::class);
     $articles = $repo->findAll();
      if (isset($_POST['search']))
       {
        $articles = $repo->recherche($_POST['s']);
       }else $articles =$articleRepository->findAll();
          
        $user = $security->getUser();
         $nbpanier=count($panierRepository->findBy(['User' => $user ]));
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $CategorieRepository->findAll(),
            'paniers' => $panierRepository->findBy(['User' => $user ]),'nbpanier' => $nbpanier
        ]);
    }





     /**
     * @Route("/article/{id}", name="catfiltre", methods={"GET"})
     */
    public function categorie(Categorie $categorie,ArticleRepository $articleRepository,Request $request,PanierRepository $panierRepository,CategorieRepository $CategorieRepository,Security $security): Response
    {

        $id=$categorie->getId();
        $user = $security->getUser();
        $nbpanier=count($panierRepository->findBy(['User' => $user ]));

        return $this->render('article/index_cat.html.twig', [
            'articles' => $articleRepository->findBy(['categorie' => $id]),
            'categories' => $CategorieRepository->findAll(),
            'paniers' => $panierRepository->findBy(['User' => $user ]),'nbpanier' => $nbpanier
        ]);
    }





    /**
     * @Route("/admin/article/new", name="article_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $article->getimage();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            // Move the file to the directory where images are stored
            try {
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $fileName
                );

            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'image' property to store the PDF file name
            // instead of its contents
            $article->setimage($fileName);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();


            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/show/{id}", name="article_show", methods={"GET","POST"})
     */
    public function show(Article $article,PanierRepository $panierRepository,Security $security,ArticleRepository $articleRepository): Response
    {
        $user = $security->getUser();
        $id=$article->getCategorie();
         $nbpanier=count($panierRepository->findBy(['User' => $user ]));
         
        if (isset($_POST['ajout'])) {
            $panier = new Panier();
            $user = $security->getUser();
            $panier->addArticle($article);
            $panier->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($panier);
            $entityManager->flush();
            return $this->redirectToRoute('article_index');

        }
        return $this->render('article/show.html.twig', [
            'articles' => $articleRepository->findBy(['categorie' => $id]),
            'article' => $article,
            'paniers' => $panierRepository->findBy(['User' => $user ]),'nbpanier' => $nbpanier
        ]);
    }

    /**
     * @Route("/admin/article/{id}/edit", name="article_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $article->getimage();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            // Move the file to the directory where images are stored
            try {
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $fileName
                );
                $article->setimage($fileName);

            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_index', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/article/{id}", name="article_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Article $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard');
    }
     /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
    
}
