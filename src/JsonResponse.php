<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace OpenEdition\EntityFishingClient ;
/**
 * Description of JsonResponse
 *
 * @author vino
 */
class JsonResponse extends \GuzzleHttp\Psr7\Response
{
    protected $parsedBody = null;
    protected $rawJson = "" ;
    
    public function getParsedBody()
    {
       if(is_null($this->parsedBody) ) {
            
            $this->rawJson = $this->getBody()->getContents() ;
            
            $this->parsedBody = json_decode( $this->rawJson );
       }
       
      
       
       if($this->parsedBody == null){
           
            throw new JsonException(" failed to decode json: " . $this->rawJson );
            
        }
        
        return $this->parsedBody ;
    }
    
    public function read( $name )
    {
        if( !isset($this->getParsedBody()->{$name} ) ){
            
            throw new JsonException(" no element in response: " . $name );
            
        }
        
        return $this->getParsedBody()->{$name} ;
    }   
    
        
        
}
class JsonException extends \Exception
{
    
}
