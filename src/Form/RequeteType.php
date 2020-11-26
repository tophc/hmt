<?php

namespace App\Form;

use App\Entity\Requete; 
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver; 
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RequeteType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('noteRequete', TextareaType::class, $this->getConfiguration("Reply", "Add reply note to request"))
                ->add('save', SubmitType::class,['label' => 'Close the request', 'attr' => ['class' => 'btn btn-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Requete::class,
            'translation_domain' => 'form'
        ]);
    }
}
