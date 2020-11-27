<?php

namespace App\Form;

use App\Entity\Chauffeur;
use App\Entity\EtatCivil;
use App\Form\ApplicationType;
use App\Form\PermisConduireType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ChauffeurType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nomChauffeur', TextType::class, $this->getConfiguration("Last name", "Driver's last name"))
                ->add('prenomChauffeur', TextType::class, $this->getConfiguration("First name", "Driver's first name"))
                ->add('emailChauffeur', EmailType::class, $this->getConfiguration("Email", "Driver's email"))
                ->add('mobileChauffeur', TextType::class, $this->getConfiguration("Mobile number", "Professional mobile phone number"))
                ->add('numeroNationalChauffeur', TextType::class, $this->getConfiguration("National registry", "Driver's national registry"))
                ->add('dateNaissanceChauffeur', DateType::class,['label' => "Date of birth", 'widget' => 'single_text' ])
                ->add('adressePostaleChauffeur', TextareaType::class, $this->getConfiguration("Address", "Driver's address"))
                ->add('genreChauffeur', ChoiceType::class, [
                    'attr' => [ 'class' => 'form-check form-check-inline'],
                    'expanded' => true,
                    'multiple'=> false,
                    'label'     => 'Gender', 
                    'choices'   => [
                        'Male'          => 'male',
                        'Female'        => 'female',
                        'Genderless'    => 'genderless'           
                    ]
                ])       
                ->add('etatCivilChauffeur',  EntityType::class, [ 
                    'class' => EtatCivil::class,
                    'label' => 'Civil status',
                    'choice_label' => 'nomEtatCivil',
                    'required' => true,
                    'placeholder' => 'Add a civil status'
                ])
                ->add('permisConduire', PermisConduireType::class, ['required' => true] )
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Chauffeur::class,
            'translation_domain' => 'form'
        ]);
    }
}
