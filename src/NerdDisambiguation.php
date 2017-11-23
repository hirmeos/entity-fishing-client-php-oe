<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient;
/**
 * Description of NerdDisambiguation
 *
 * @author vinogradov
 */
class NerdDisambiguation extends GenericEntity
{
    //put your code here
    const FLAG_DISAMBIGUATION_COMPLETED = 1 ;
    
    protected $entities ;
    protected $requests_done = 0;
    
    public function getEntities()
    {
        $json = json_decode($this->entities);
        
        if ($json !== null){
            
            return $json ;
        }
        
        return array();
    }
    
    public function setEntities( $entities )
    {
        $this->entities = $entities ;
    }
    
    
    
    
}
