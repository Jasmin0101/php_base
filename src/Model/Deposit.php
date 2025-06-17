<?php

namespace App\Model;

class Deposit{

    public  readonly float $rate;

    public  readonly  string $description;

    public  readonly  string $monthPublication;

    public function  __construct( array $rawDataElem , array $headerData)
    {
        
    $this -> monthPublication = $rawDataElem['dt'];
    
        $this  -> rate = (float) $rawDataElem['obs_val'];

     $this -> description= current( array_filter(  $headerData , function ($headerDataElem) use ($rawDataElem) {

            return $headerDataElem['id'] === $rawDataElem['element_id'];
            
        })
    )['elname'];

    }
  

}