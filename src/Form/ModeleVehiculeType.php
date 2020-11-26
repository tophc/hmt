<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\ModeleVehicule;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ModeleVehiculeType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nomModeleVehicule', TextType::class, $this->getConfiguration("Model name", "The name of the model"))
                ->add('marqueModeleVehicule', TextType::class, $this->getConfiguration("Brand", "The model brand"))
                ->add('capaciteModeleVehicule', NumberType::class, $this->getConfiguration("Capacity", "Maximum mass allowed"))
                ->add('intervalleEntretienModeleVehicule', NumberType::class, $this->getConfiguration("Maintenance interval", "Mileage between each maintenances"))
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ModeleVehicule::class,
            'translation_domain' => 'form'
        ]);
    }
}
