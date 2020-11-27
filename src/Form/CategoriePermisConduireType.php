<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\CategoriePermisConduire;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CategoriePermisConduireType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nomCategoriePermisConduire', TextType::class, $this->getConfiguration("Category name", "Oficial category name",  ['required' => true] ))
                ->add('infoCategoriePermisConduire', TextareaType::class, $this->getConfiguration("Description", "A brief description of the category"))
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CategoriePermisConduire::class,
            'translation_domain' => 'form'
        ]);
    }
}
