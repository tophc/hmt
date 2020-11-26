<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;

class ApplicationType extends AbstractType{

    /**
     * Permet d'avoir la configuration de base d'un champ de formulaire ainsi que la traducion
     *
     * @param string $label
     * @param string $placeholder
     * @param array $option
     * 
     * @return array
     */
    protected function getConfiguration($label, $placeholder, $option = []) {
        //array_merge permet de fusionner les 2 tableaux ($option s'il y en a)
        return array_merge_recursive(['label' => $label,  'attr' => ['placeholder' => $placeholder]], $option);
    }  
}