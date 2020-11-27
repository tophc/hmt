<?php

namespace App\Form;

use App\Entity\Logistique;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LogistiqueType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nomLogistique', TextType::class, $this->getConfiguration("Last name", "Logistics last name"))
                ->add('prenomLogistique', TextType::class, $this->getConfiguration("First name", "Logistics first name"))
                ->add('emailLogistique', EmailType::class, $this->getConfiguration("Email", "Logistics email"))
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Logistique::class,
            'translation_domain' => 'form'
        ]);
    }
}
