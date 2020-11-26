<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Service\Upload\Fichier;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FichierType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fichier', FileType::class, $this->getConfiguration('File', 'Select a file', ['required' => true] )) 
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([  
            'data_class' => Fichier::class,
            'translation_domain' => 'form'
        ]);
    }
} 
