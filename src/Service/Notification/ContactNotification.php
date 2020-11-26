<?php

namespace App\Service\Notification;

use Twig\Environment;
use App\Service\Notification\Contact;

class ContactNotification
{ 

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    /**
     * @param \Swift_Mailer $mailer
     * @param Environment $renderer
     */
    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    { 
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    /**
     * @param Contact $contact
     * @param int $idChauffeur
     */
    public function sendEmail(Contact $contact, $idChauffeur)
    {  
        $message = (new \Swift_Message('Object : '.$contact->getSujet()) )        
            ->setFrom($contact->getEmail())
            ->setTo($contact->getService().'@hayam.be')
            ->setReplyTo($contact->getEmail())
            ->setBody($this->renderer->render('chauffeur/mail.html.twig', ['contact' => $contact]), 'text/html' );
            
            if ($contact->getFichier()){
 
                $message->attach(\Swift_Attachment::fromPath('../uploads/chauffeur/'.$idChauffeur.'/'. $contact->getNomFichier()));
            }
            
        $this->mailer->send($message);    
    }
}