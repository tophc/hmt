<?php

namespace App\Form;

use App\Entity\Vehicule;
use App\Form\ApplicationType;
use App\Entity\ModeleVehicule;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class VehiculeType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('immatriculationVehicule', TextType::class, $this->getConfiguration("Vehicle numberplate", 'The vehicle numberplate'))
                ->add('numChassisVehicule', TextType::class, $this->getConfiguration("Vehicle frame number", "The vehicle frame number"))
                ->add('modeleVehicule', EntityType::class, ['class'=>ModeleVehicule::class, 'choice_label' => 'nomModeleVehicule', 'label' => "Vehicle model"])    
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
            'translation_domain' => 'form'
        ]);
    }
}
