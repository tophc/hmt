<?php

namespace App\Form;

use App\Entity\Tournee;
use App\Entity\CodePostal;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CodePostalType extends ApplicationType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('numCodePostal', NumberType::class, $this->getConfiguration("Postal code", "Enter a valid postal code number"))
                ->add('localiteCodePostal', TextType::class, $this->getConfiguration("Locality", "Enter the postal code number locality"))
                ->add('tournee', EntityType::class, [
                    'class' => Tournee::class,
                    'query_builder' => function (EntityRepository $repo) {
                        return $repo->createQueryBuilder('t')
                            ->orderBy('t.numTournee', 'ASC');
                    },   
                    'label' => 'Round',
                    'choice_label' => 'numTournee',
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'Add a round'             
                ])
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])  
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CodePostal::class,
            'translation_domain' => 'form'
        ]);
    }
}
