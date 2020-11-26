<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class TraductionEtatService 
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Permet de traduire ledescriptifEtat  envoyé en paramètre 
     *
     * @param string $descriptifEtat
     * 
     * @return string
     */
    public function traductionDescriptifEtat($descriptifEtat): String
    {
        switch($descriptifEtat)
        {
            case '000'  : $descriptifEtat = $this->translator->trans('Parcel taken in charge'); break;
            case '001'  : $descriptifEtat = $this->translator->trans('On the way to delivery (first pass)'); break;
            case '002'  : $descriptifEtat = $this->translator->trans('Failure first pass'); break;
            case '003'  : $descriptifEtat = $this->translator->trans('Waiting for second pass'); break;
            case '004'  : $descriptifEtat = $this->translator->trans('On the way to delivery (second passage)'); break;
            case '005'  : $descriptifEtat = $this->translator->trans('Parcel refused by the recipient'); break;
            case '006'  : $descriptifEtat = $this->translator->trans('Error address'); break;
            case '007'  : $descriptifEtat = $this->translator->trans('Return to sender'); break;
            case '008'  : $descriptifEtat = $this->translator->trans('Awaiting information (investigation in progress)'); break;
            case '009'  : $descriptifEtat = $this->translator->trans('Lost parcels'); break;
            case '100'  : $descriptifEtat = $this->translator->trans('On the way to the sorting center'); break;
            case '999'  : $descriptifEtat = $this->translator->trans('Parcel delivered'); break;      
            default         : $descriptifEtat = $this->translator->trans('unknow');          
        }       

       return $descriptifEtat;
    }
}