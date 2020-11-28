<?php

namespace App\Form;

use App\Entity\Amende;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\DataTransformer\ImmatriculationToVehiculeTransformer;

class AmendeType extends ApplicationType 
{
    private $transformer;
    
    public function  __construct(ImmatriculationToVehiculeTransformer $transformer){
        
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateAmende', DateTimeType::class, [ 'label' => "Date of offense", 'years' => range(date('Y'), date('Y')-10)] )
                ->add('numAmende', TextType::class, $this->getConfiguration('Number', 'Reference number of the fine'))
                ->add('montantAmende', MoneyType::class, $this->getConfiguration('Amount', 'Amount of the fine'))
                ->add('remarqueAmende', TextareaType::class, $this->getConfiguration('Note', 'Add a note (optional)', ['required' => false])) 
                ->add('vehicule', TextType::class, $this->getConfiguration('Vehicle', 'Search a vehicle by platenumber'))
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])          
        ;

        $builder->get('vehicule')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Amende::class,   
            'translation_domain' => 'form'     
        ]);
    } 
}
