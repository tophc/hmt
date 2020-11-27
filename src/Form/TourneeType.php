<?php

namespace App\Form;

use App\Entity\Tournee;
use App\Entity\CodePostal;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TourneeType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        $builder->add('numTournee', TextType::class, $this->getConfiguration('Round number',  'Add a round number' ))
                ->add('infoTournee', TextareaType::class, $this->getConfiguration('Informations', 'Add a short description' ))
                ->add('codePostals',  EntityType::class, [ 
                    'attr' => [ 'class' => "row"],         
                    'class' => CodePostal::class,          
                    'label' => 'Postal code',
                    'by_reference' => false,
                    'choice_label' => 'numCodePostal',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true,  
                ])
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])   
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tournee::class,
            'translation_domain' => 'form'
        ]);
    }
}
