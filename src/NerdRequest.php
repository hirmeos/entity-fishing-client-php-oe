<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient ;

use GuzzleHttp\Psr7\ServerRequest ;
use GuzzleHttp\Psr7\MultipartStream ;

/**
 * Description of NerdRequest
 *
 * @author vinogradov
 */
class NerdRequest extends ServerRequest 
{
    
    const MAX_SENTENCES = 50;
    private $boundary ;
    
    public function __construct()
    {
       
        $this->boundary = "---------------------" . md5(mt_rand() . microtime());
        
    }
    
     
    public function getBody()
    {
       
        $json = json_encode((object) $this->getAttributes() );
        
        $multipart = array(array("name" => "query", "contents" => $json) );
        
        $stream = new MultipartStream($multipart, $this->boundary );
        
        return $stream ;
        
    }
    
    public function getBoundary()
    {
        return $this->boundary ;
    }
    
    public function getText()
    {
        $processSentences = $this->getAttribute(EntityFishing::PROCESS_SENTENCE, false) ;
        
        $text = $this->getAttribute(EntityFishing::TEXT, "" );
        
        $result = "" ;
        
        if($processSentences != false){
            
            $sentences = $this->getAttribute(EntityFishing::SENTENCES);
            
            foreach($processSentences as $key){
                
                $sentence = $sentences[$key] ;
                 
                $result .= mb_substr($text, $sentence->offsetStart,$sentence->offsetEnd) ;
            }
            
            return $result ;
        }
        
        return $text ;
    }
    
    
    
}
