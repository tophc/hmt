<?php

namespace App\Form;

use App\Entity\Etat;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EtatType extends ApplicationType 
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codeEtat', TextType::class, $this->getConfiguration("Code", "Status code"))
            ->add('descriptifEtat', TextareaType::class, $this->getConfiguration("Description", "Add a short description")) 
            ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Etat::class,
            'translation_domain' => 'form'
        ]);
    }
}
