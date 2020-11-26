<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil du site
     * 
     * @Route("/", name="/")
     * @Route("/home", name="home")
     *  
     * @param TranslatorInterface $translator
     * 
     * @return Response
     */
    public function homePage(TranslatorInterface $translator): Response
    {
        return $this->render('home/home.html.twig',[
            'titre' => $translator->trans('Welcome')
        ]);
    }

    /**
     * Affiche la page d'aide
     * 
     * @Route("/home/help", name="home-help")
     *  
     * @param TranslatorInterface $translator
     * 
     * @return Response
     */
    public function help(TranslatorInterface $translator): Response
    {
        return $this->render('home/help.html.twig',[
            'titre' => $translator->trans('Help')
        ]);
    }

    /**
     * Affiche la page d'informations
     * 
     * @Route("/home/about", name="home-about")
     *  
     * @param TranslatorInterface $translator
     * 
     * @return Response
     */
    public function about(TranslatorInterface $translator): Response
    {
        return $this->render('home/about.html.twig',[
            'titre' => $translator->trans('About')
        ]);
    }

    /**
     * Permet de changer la langue du site
     * 
     * @Route ("/traduire/{locale}", name ="traduire")
     * 
     * @param string $locale
     * @param Request $request
     * 
     * @return Response
     */
    public function traduire($locale, Request $request): Response
    {
        $request->getSession()->set('_locale', $locale);

        return $this->redirect($request->headers->get('referer')); 
    }
}
