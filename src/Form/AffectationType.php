<?php

namespace App\Form;

use App\Entity\Tournee;
use App\Entity\Vehicule;
use App\Entity\Chauffeur;
use App\Entity\Affectation;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Repository\ChauffeurRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\DataTransformer\IdChauffeurToChauffeurTransformer;


class AffectationType extends ApplicationType
{
    private $transformer;
    private $repoChauffeur; 

    public function  __construct(IdChauffeurToChauffeurTransformer $transformer, ChauffeurRepository $repoChauffeur){
        
        $this->transformer = $transformer;
        $this->repoChauffeur = $repoChauffeur;
    }

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
                ->add('vehicule', EntityType::class, [
                    'class' => Vehicule::class,
                    'query_builder' => function (EntityRepository $repo) {
                        return $repo->createQueryBuilder('v')  
                            ->andWhere('v.statutVehicule = 1')                     
                            ->orderBy('v.immatriculationVehicule', 'ASC');
                    },   
                    'label' => 'Vehicle',
                    'placeholder' => false,
                    'placeholder' => 'Choose a vehicle',
                    'choice_label' => function (Vehicule $vehicule){
                       return $vehicule->getImmatriculationVehicule().' (MMA: '. $vehicule->getModeleVehicule()->getCapaciteModeleVehicule().')';
                    }
                ])
                ->add('chauffeur', EntityType::class, [
                    'class' => Chauffeur::class,
                    'choices' => null,
                    'by_reference' => true,   
                    'label' => 'Driver',
                    'choice_label' => 'nomChauffeur'   
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
  
        $builder->get('chauffeur')->addModelTransformer($this->transformer); 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Affectation::class,
            'translation_domain' => 'form'
        ]);
    }
}
