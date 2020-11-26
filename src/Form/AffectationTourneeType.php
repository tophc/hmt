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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AffectationTourneeType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                ->add('chauffeur', EntityType::class, [
                    'class' => Chauffeur::class,
                    'query_builder' => function (EntityRepository $repo) {
                        return $repo->createQueryBuilder('c')
                            ->andWhere('c.statutChauffeur = 1')
                            ->orderBy('c.nomChauffeur', 'ASC');
                    },
                    'label' => 'Driver',
                    'choice_label' => function ($chauffeur) {
                        return $chauffeur->getNomChauffeur().' '.$chauffeur->getPrenomChauffeur() ;
                    }
                ])
                ->add('vehicule', EntityType::class, [
                    'class' => Vehicule::class,
                    'query_builder' => function (EntityRepository $repo) {
                        return $repo->createQueryBuilder('v')  
                            ->andWhere('v.statutVehicule = 1')                     
                            ->orderBy('v.immatriculationVehicule', 'ASC');
                    },   
                    'label' => 'Vehicle',
                    'choice_label' => 'immatriculationVehicule'
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
