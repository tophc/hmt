<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\PermisConduire; 
use App\Entity\CategoriePermisConduire;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PermisConduireType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('numPermisConduire', TextType::class, $this->getConfiguration("License number", "Driver licence number"))
                ->add('dateValPermisConduire', DateType::class,['label' => "Date of validity", 'widget' => 'single_text'])
                ->add('categoriePermisConduires', EntityType::class, [
                    'attr' => [ 'class' => 'form-check form-check-inline'],
                    'class' => CategoriePermisConduire::class,              
                    'label' => 'Category',
                    'by_reference' => false,
                    'choice_label' => 'nomCategoriePermisConduire',
                    'expanded' => true,
                    'multiple' => true, 
                    'required' => true,  
                    'empty_data' => false 
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PermisConduire::class,
            'translation_domain' => 'form'  
        ]);
    }
}
