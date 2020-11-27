<?php

namespace App\Form;

use App\Entity\Tournee;
use App\Entity\Vehicule;
use App\Entity\Affectation;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AffectationChauffeurType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->mma = $options['mma'];

        $builder->add('dateAffectation', DateType::class, [
                    'label' => "Start date", 
                    'widget' => 'single_text' 
                ]) 
                ->add('dateFin', DateType::class, [
                    'mapped' => false, 
                    'required' => false, 
                    'label' => "End date for multiple assignment (optionnal)", 
                    'widget' => 'single_text' 
                ])
                ->add('vehicule', EntityType::class, [
                    'class' => Vehicule::class,
                    'query_builder' => function (EntityRepository $repo) {
                        return $repo->createQueryBuilder('v')   
                            ->andWhere('v.statutVehicule = 1')  
                            ->leftJoin('v.modeleVehicule' , 'm') 
                            ->andWhere('m.capaciteModeleVehicule <= :mma')
                            ->setParameter('mma', $this->mma)                  
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
                            ->orderBy('t.infoTournee', 'ASC'); 
                    },   
                    'label' => 'Round',
                    'choice_label' => function (Tournee $tournee){
                        return $tournee->getnumTournee().' - '.$tournee->getInfoTournee();
                    }
                ])  
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Affectation::class,
            'translation_domain' => 'form',
            'mma' => null
        ]);
    }
}
