<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\ControleTechnique;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ControleTechniqueType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateControleTechnique', DateType::class,['required' => true, 'label' => "Date", 'widget' => 'single_text'])
                ->add('statutControleTechnique', ChoiceType::class,  [
                    'attr'      => [ 'class' => 'form-check form-check-inline'],
                    'label'     => 'Choose an option',
                    'choices'   => ['Passed'    => false, 'Refused'   => true],
                    'expanded'  => true,
                    'multiple'  => false, 
                    'required'  => true,
                ])
                ->add('motifs', CollectionType::class, [
                    'required'      => false,
                    'mapped'        => false,
                    'entry_type'    => TextType::class,  
                    'entry_options' => ['attr' => ['placeholder' => "Add a reason for refusal"]],
                    'allow_add'     => true,
                    'allow_delete'  => true,
                ])
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ControleTechnique::class,
            'translation_domain' => 'form'
        ]);
    }
}
