<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace EntityFishingClient;

/**
 * Description of GenericEntity
 *
 * @author vinogradov
 */
class GenericEntity implements IGenericEntity
{
    //put your code here
     public function __set($name, $value)
    {
        $this->{$name} = $value;
    }
    
    public function __get( $name )
    {
        if( isset($this->{$name}) ){
            
            return $this->{$name} ;
        }   
        
        throw new EmptyFieldException ("unset field " . $name . print_r($this,true) ) ;
        
    }
}
