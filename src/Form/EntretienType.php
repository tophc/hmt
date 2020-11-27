<?php

namespace App\Form;
 
use App\Entity\Entretien;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EntretienType extends ApplicationType 
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateEntretien', DateType::class,['required' => true, 'label' => "Date", 'widget' => 'single_text'])
                ->add('kmEntretien', NumberType::class, $this->getConfiguration("Mileage", "Add vehicle mileage"))
                ->add('montantEntretien', MoneyType::class, $this->getConfiguration("Amount", "Amount of the maintenance"))
                ->add('remarqueEntretien', TextareaType::class, $this->getConfiguration("Note", "Add a note (optional)", ['required' => false])) 
                ->add('save', SubmitType::class,['label' => 'Validate', 'attr' => ['class' => 'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entretien::class,
            'translation_domain' => 'form'
        ]);
    }
}
