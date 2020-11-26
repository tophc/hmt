<?php

namespace App\Form;

use App\Entity\Tournee;
use App\Entity\Vehicule;
use App\Entity\Chauffeur;
use App\Entity\Affectation;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AffectationEditType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('chauffeur', EntityType::class, [
                    'class' => Chauffeur::class,
                    'choices' => null,
                    'by_reference' => true,
                    'label' => 'Driver',
                    'choice_label' => 'nomChauffeur'   
                ])
                ->add('vehicule', EntityType::class, [
                    'class' => Vehicule::class,
                    'query_builder' => function (EntityRepository $repo) {
                        return $repo->createQueryBuilder('v')  
                            ->andWhere('v.statutVehicule = 1')                     
                            ->orderBy('v.immatriculationVehicule', 'ASC');
                    },   
                    'label' => 'Vehicle',
                    'choice_label' => function (Vehicule $vehicule){
                        return $vehicule->getImmatriculationVehicule().' (MMA: '. $vehicule->getModeleVehicule()->getCapaciteModeleVehicule().')';
                     }
                ])
                ->add('tournee', EntityType::class, [
                    'class' => Tournee::class,
                    'query_builder' => function (EntityRepository $repo) {
                        return $repo->createQueryBuilder('t')                      
                            ->orderBy('t.numTournee', 'ASC');
                    },   
                    'label' => 'Round',
                    'choice_label' => function (Tournee $tournee){
                        return $tournee->getnumTournee().' - '.$tournee->getInfoTournee();
                    }
                ]) 
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Affectation::class,
            'translation_domain' => 'form'
        ]);
    }
}
