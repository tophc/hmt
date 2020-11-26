<?php

namespace App\Form;

use App\Form\ApplicationType; 
use App\Service\Notification\Contact;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sujet', TextType::class, $this->getConfiguration("Object", "Request object"))
            ->add('message', TextareaType::class, $this->getConfiguration("Message", "Your message"))
            ->add('fichier', FileType::class, $this->getConfiguration('File', 'Select a file', ['required' => false] )) 
            ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-dark']])   
        ; 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'translation_domain' => 'form'
        ]);
    }
}
