<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class TraductionDateService 
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Permet de traduire le nom du mois envoyé en paramètre en version courte (sep, oct...)
     *
     * @param string $mois
     * 
     * @return string
     */
    public function traductionDateMoisCourt($mois): String
    {
        switch($mois)
        {
            case 'January'  : case 'Jan' : case '01' :  case '1' : $mois = $this->translator->trans('Jan'); break;
            case 'February' : case 'Feb' : case '02' :  case '2' : $mois = $this->translator->trans('Feb'); break;
            case 'March'    : case 'Mar' : case '03' :  case '3' : $mois = $this->translator->trans('Mar'); break;
            case 'April'    : case 'Apr' : case '04' :  case '4' : $mois = $this->translator->trans('Apr'); break;
            case 'May'      : case 'May' : case '05' :  case '5' : $mois = $this->translator->trans('May'); break;
            case 'June'     : case 'Jun' : case '06' :  case '6' : $mois = $this->translator->trans('Jun'); break;
            case 'July'     : case 'Jul' : case '07' :  case '7' : $mois = $this->translator->trans('Jul'); break;
            case 'August'   : case 'Aug' : case '08' :  case '8' : $mois = $this->translator->trans('Aug'); break;
            case 'September': case 'Sep' : case '09' :  case '9' : $mois = $this->translator->trans('Sep'); break;
            case 'October'  : case 'Oct' : case '10' : $mois = $this->translator->trans('Oct'); break;
            case 'November' : case 'Nov' : case '11' : $mois = $this->translator->trans('Nov'); break;
            case 'December' : case 'Dec' : case '12' : $mois = $this->translator->trans('Dec'); break;      
            default         : $mois = $this->translator->trans('unknow');          
        }       

       return $mois;
    }

    /**
     * Permet de traduire le nom du mois envoyé en paramètre en version longue (sep, oct...)
     *
     * @param string $mois
     * 
     * @return string
     */
    public function traductionDateMoisLong($mois): String
    {
        switch($mois)
        {   case 'January'  : case 'Jan' : case '01' :  case '1' : $mois = $this->translator->trans('January');     break;
            case 'February' : case 'Feb' : case '02' :  case '2' : $mois = $this->translator->trans('February');    break;
            case 'March'    : case 'Mar' : case '03' :  case '3' : $mois = $this->translator->trans('March');       break;
            case 'April'    : case 'Apr' : case '04' :  case '4' : $mois = $this->translator->trans('April');       break;
            case 'May'      : case 'May' : case '05' :  case '5' : $mois = $this->translator->trans('May');         break;
            case 'June'     : case 'Jun' : case '06' :  case '6' : $mois = $this->translator->trans('June');        break;
            case 'July'     : case 'Jul' : case '07' :  case '7' : $mois = $this->translator->trans('July');        break;
            case 'August'   : case 'Aug' : case '08' :  case '8' : $mois = $this->translator->trans('August');      break;
            case 'September': case 'Sep' : case '09' :  case '9' : $mois = $this->translator->trans('September');   break;
            case 'October'  : case 'Oct' : case '10' : $mois = $this->translator->trans('October');     break;
            case 'November' : case 'Nov' : case '11' : $mois = $this->translator->trans('November');    break;
            case 'December' : case 'Dec' : case '12' : $mois = $this->translator->trans('December');    break;      
            default         : $mois = $this->translator->trans('unknow');          
        }       

       return $mois;
    }

    /**
     * Permet de traduire les jours de la semaine envoyés en paramètre en version courte (Mon, Tue...)
     *
     * @param string $jour
     * 
     * @return string
     */
    public function traductionDateJourCourt($jour): String
    {
        switch($jour)
        {   case 'Monday'   : case 'Mon'    : $jour = $this->translator->trans('Mon');  break;
            case 'Tuesday'  : case 'Tue'    : $jour = $this->translator->trans('Tue');  break;
            case 'Wednesday': case 'Wed'    : $jour = $this->translator->trans('Wed');  break;
            case 'Thursday' : case 'Thu'    : $jour = $this->translator->trans('Thu');  break;
            case 'Friday'   : case 'Fri'    : $jour = $this->translator->trans('Fri');  break;
            case 'Saturday' : case 'Sat'    : $jour = $this->translator->trans('Sat');  break;
            case 'Sunday'   : case 'Sun'    : $jour = $this->translator->trans('Sun');  break;
            default         : $mois = $this->translator->trans('unknow');          
        }       

       return $mois;
    }

    /**
     * Permet de traduire les jours de la semaine envoyés en paramètre en version longue (Mon, Tue...)
     *
     * @param string $jour
     * 
     * @return string
     */
    public function traductionDateJourLong($jour): String
    {
        switch($jour)
        {   case 'Monday'   : case 'Mon'    : $jour = $this->translator->trans('Monday');       break;
            case 'Tuesday'  : case 'Tue'    : $jour = $this->translator->trans('Tuesday');      break;
            case 'Wednesday': case 'Wed'    : $jour = $this->translator->trans('Wednesday');    break;
            case 'Thursday' : case 'Thu'    : $jour = $this->translator->trans('Thursday');     break;
            case 'Friday'   : case 'Fri'    : $jour = $this->translator->trans('Friday');       break;
            case 'Saturday' : case 'Sat'    : $jour = $this->translator->trans('Saturday');     break;
            case 'Sunday'   : case 'Sun'    : $jour = $this->translator->trans('Sunday');       break; 
            default         : $mois = $this->translator->trans('unknow');          
        }       

       return $mois;
    }
}