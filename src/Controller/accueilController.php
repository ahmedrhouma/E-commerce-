<?php  // src/Controller/InscriptionController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use Symfony\Component\Security\Core\Security;
use App\Repository\PanierRepository;




Class accueilController extends Controller
{

    /**
     * @Route("/register/confirmed", name="register confirmed")
    */

    public function register(Request $request)
    {  

       
        $router = $this->container->get('router');
        return new RedirectResponse($router->generate('Accueil'), 307);

    }

    /**
     * @Route("/", name="home")
    */
    public function homeAction(Request $request,PanierRepository $panierRepository,Security $security)
    {
        $authChecker = $this->container->get('security.authorization_checker');
        $router = $this->container->get('router');
        $user = $security->getUser();
        $nbpanier=count($panierRepository->findBy(['User' => $user ]));
        if ($authChecker->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($router->generate('dashboard'), 307);
        }else return $this->render('accueil/home.html.twig',['paniers' => $panierRepository->findBy(['User' => $user ]),'nbpanier' => $nbpanier]);

    }
    /**
     * @Route("/admin/dashboard", name="dashboard")
     * 
    */

    public function liste(Request $request,ArticleRepository $articleRepository,CommandeRepository $commandeRepository,
        CategorieRepository $categorieRepository)
    {   
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        $nbuser=count($users);

        $nbarticle=count($articleRepository->findAll());

        $nbcommande=count($commandeRepository->findAll());

        $nbcategorie=count($categorieRepository->findAll());

       return $this->render('accueil/dashboard.html.twig', [
            'articles' => $articleRepository->findAll(),'nbuser' => $nbuser,'nbarticle' => $nbarticle , 'nbcommande' => $nbcommande , 'nbcategorie' => $nbcategorie 
        ]); 

    }
}
?>