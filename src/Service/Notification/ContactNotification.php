<?php

namespace App\Service\Notification;

use App\Service\Notification\Contact;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ContactNotification
{ 
    private $mailer;

    /**
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    { 
        $this->mailer = $mailer;
    }

    /**
     * @param Contact $contact
     * @param int $idChauffeur
     */
    public function sendEmail(Contact $contact, $idChauffeur)
    {  
        $email = (new TemplatedEmail())        
            ->from($contact->getEmail())
            ->to($contact->getService().'@devstation.be')
            ->subject($contact->getSujet())
            ->replyTo($contact->getEmail())
            ->htmlTemplate('chauffeur/mail.html.twig')
            ->context(['contact' => $contact]);
            
            
            if ($contact->getFichier())
            {
                $email->attachFromPath('../uploads/chauffeur/'.$idChauffeur.'/'. $contact->getNomFichier());
            }
            
        $this->mailer->send($email);    
    }
}