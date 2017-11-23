<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient ;
/**
 * Description of NerdInputJsonAdapter
 *
 * @author vinogradov
 */
class NerdInputJsonAdapter
{
    //put your code here
    protected $body;
    protected $parsedBody ;
    
    public function __construct( $string )
    {
       
        $this->body = $string ;
        
        $this->parsedBody = json_decode( $this->body );
        
        if($this->parsedBody == null){
            throw new InvalidDataException(self::class ." failed to decode json: " .$this->body );
        }
    }
    
    public function getEntities()
    {
        $entities = $this->parsedBody->{ INerdAttributes::ENTITIES } ;
                   
        if(count( $entities ) == 0){
            throw new InvalidDataException(self::class . "empty entities");
        }
            
        return $entities ;
    }
    
}
