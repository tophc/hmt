<?php

namespace App\Form;

use App\Entity\Tournee;
use App\Entity\Chauffeur;
use App\Entity\Affectation;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
 
class AffectationVehiculeType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->categoriePermis = $options['categoriePermis'];

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
                            ->leftJoin('c.permisConduire', 'p')
                            ->leftJoin('p.categoriePermisConduires', 'x')
                            ->andWhere( 'x.nomCategoriePermisConduire IN (:cat)')
                            ->setParameter('cat', $this->categoriePermis)
                            ->orderBy('c.nomChauffeur', 'ASC');
                    },
                    'label' => 'Driver',
                    'choice_label' => function ($chauffeur) {
                        $tableauCategories = array();
                        foreach ($chauffeur->getPermisConduire()->getCategoriePermisConduires() as $categorie)
                        {
                            $tableauCategories[] =  $categorie->getNomCategoriePermisConduire();
                        }
                        if (empty($tableauCategories)) $tableauCategories = 'No licence';
                        return $chauffeur->getNomChauffeur().' '.$chauffeur->getPrenomChauffeur().' | ('.implode(', ', $tableauCategories).')' ;
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
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Affectation::class,
            'translation_domain' => 'form',
            'categoriePermis' => null
        ]);
    }
}
