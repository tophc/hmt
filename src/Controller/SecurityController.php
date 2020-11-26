<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * Permet d'afficher et de gérer le formulaire de connexion pour les chauffeurs/Secretariat/logistique
     * 
     * @Route("/chauffeur/login", name="chauffeur_login")
     * @Route("/secretariat/login", name="secretariat_login")
     * @Route("/logistique/login", name="logistique_login")
     * @Route("/administration/login", name="administration_login")
     * 
     * @param AuthenticationUtils $utils
     * @param Request $request
     * @param TranslatorInterface $translator
     * 
     * @return Response 
     */
    public function login(AuthenticationUtils $utils, Request $request, TranslatorInterface $translator): Response
    {
        $error = $utils->getLastAuthenticationError(); //# si il y a des erreur renvoie un objet sinon renvoi null
        $username = $utils->getLastUsername();

        if ($request->get('_route') == "chauffeur_login")$titre = $translator->trans('Driver login');
        elseif ($request->get('_route') == "secretariat_login") $titre = $translator->trans('Secretariat login');
        elseif ($request->get('_route') == "logistique_login") $titre = $translator->trans('Logistics login');
        elseif ($request->get('_route') == "administration_login") $titre = $translator->trans('Administration login');
        else return $this->redirectToRoute('home');

        return $this->render('security/login.html.twig' , [
            'titre' => $titre,
            'hasError' => $error !== null,
            'username' => $username
        ]);
    }

    /**
     * Permet de se déconnecter
     * 
     * @Route("/account/logout", name="account_logout")
     * 
     * @return void
     */
    public function logout(): void
    {
        // Symfony gère la déconnexion grâce à la route (voir security.yaml)
    }
    
    /**
     * Permet de modifier le mot de passe 
     * 
     * @Route("/chauffeur/password-update", name="chauffeur_password")
     * @Route("/secretariat/password-update", name="secretariat_password")
     * @Route("/logistique/password-update", name="logistique_password") 
     * @Route("/administration/password-update", name="administration_password")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @param TranslatorInterface $translator
     * 
     * @return Response
     */
    public function modifierPassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder , TranslatorInterface $translator): Response
    {   
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //1. Verifier si le mot de passe actuel est correcte 
            //la fonction: password_verify('password',$2y$13$I73FpSFVkGxsVrZJXMfso.X9KJ74F.xxONbCXLKRM1Z)) (php function  [PHP 5 >= 5.5.0, PHP 7] :  Vérifie qu'un mot de passe correspond à un hachage)
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())) {
                //gérer l'erreur, on peut pas le faire dans l'entité ModificationMotDePasse, il faudra le faire manuellement avec l'api symfoni "form" (un champ de formulaire est aussi unformulaire, une instance de la classe Form)
                $form->get('oldPassword')->addError(new FormError($translator->trans('The password entered does not match the current password')));
            }
            else
            {
                $newPassword = $passwordUpdate->getNewPassword();
                $passwordCrypte = $encoder->encodePassword($user, $newPassword);

                $user->setPassword($passwordCrypte);
                
                // Si le mot de passe est modifier on enlève le rôle "ROLE_NEW_USER"
                $roles = $user->getRoles();

                // Cherche le rôle "ROLE_NEW_USER"
                if (in_array("ROLE_NEW_USER", $roles)) 
                {   
                    // retire le role "ROLE_NEW_USER"
                    unset($roles[array_search("ROLE_NEW_USER", $roles)]);
                    // Trie le tableau pour corriger l'indexation
                    sort($roles);
                    $user->setRoles($roles); 
                }
                
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    $translator->trans('The new password has been saved')
                );
                  
                if ($request->get('_route') == "chauffeur_password") $route = "chauffeur_dashboard"; 
                elseif ($request->get('_route') == "secretariat_password") $route = "secretariat_dashboard";
                elseif ($request->get('_route') == "logistique_password") $route = "logistique_dashboard";
                elseif ($request->get('_route') == "administration_password") $route = "administration_dashboard";
                else $route = "home";

                return $this->redirectToRoute($route);
            }
        }

        return $this->render('security/password.html.twig', [
            'titre' => $translator->trans('Change password'),
            'form' => $form->createView()
        ]);
    }
}