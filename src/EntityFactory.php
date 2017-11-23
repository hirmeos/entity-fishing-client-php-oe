<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace EntityFishingClient;

use GuzzleHttp\Psr7\Stream ;

/**
 * Description of EntityFactory
 *
 * @author vinogradov
 */
class EntityFactory
{
    public function fromCliArguments($argv = array(), $separator = "=" )
    {
        
        $document = new DocumentEntity();
        
        $argv = array_slice($argv, 1);
        
        $params = array();
        
        foreach($argv as $arg){
            $exploded = explode($separator,$arg);
            if(count($exploded) == 2){
                $name = $exploded[0];
                $value = $exploded[1];
                $params[$name] = $value;
            }
            
        }
        
        if(isset($params["text"])){
            
            $document->__set("text", $params["text"] ) ;
            
        }
        
        if(isset($params["file"])){
            
            $resource = fopen($params["file"], 'r');
            
            $stream = new Stream($resource);
            
            $document->__set("html", $stream->getContents() );
            
            $stream->close() ;
            
        }
        
        
        if(isset($params["html"])){
            
            $document->__set("html", $params["html"] ) ;
            
        }
        
        if(isset($params["lang"])){
            
            $document->__set("languages", array($params["lang"]) ) ;
            
        }

        return $document ;
                                     
        
    }
}
