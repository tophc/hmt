<?php

namespace App\Form; 

use App\Entity\Secretariat;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SecretariatType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nomSecretariat', TextType::class, $this->getConfiguration("Last name", "Secretariat last name"))
                ->add('prenomSecretariat', TextType::class, $this->getConfiguration("First name", "Secretariat first name"))
                ->add('emailSecretariat', EmailType::class, $this->getConfiguration("Email", "Secretariat email"))
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Secretariat::class,
            'translation_domain' => 'form'
        ]);
    }
}
