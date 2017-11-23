<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient ;
/**
 * Description of SolariumEntity
 *
 * @author vinogradov
 */
class DocumentEntity extends GenericEntity implements IDocumentEntity 
{
    //put your code here
    protected $text;
    protected $languages = array("en");
    protected $html ;
      
    public function getText()
    {
        return $this->__get("text") ;
    }
   
    
    public function getLanguages()
    {
        return $this->__get("languages") ;
    }   
    
    public function getHtml()
    {
        return $this->__get( "html" );
    }
    
    public function getParagraphs()
    {
        $dom = new \DOMDocument("1.0", "UTF-8") ;
        
        try{
            $dom->loadHTML(  mb_convert_encoding( $this->getHtml(), 'HTML-ENTITIES', 'UTF-8' ) );
        } 
        catch (\DOMException $ex) {
            throw new EmptyFieldException("loading entity html failed" );
        }
                
        $paragraphs = $dom->getElementsByTagName("p") ;
        
        $result = array();
        
        foreach($paragraphs as $paragraph){
               
            $str = $paragraph->nodeValue ;
            
            $str = mb_convert_encoding($str, "HTML-ENTITIES", "UTF-8" ); 
            
            $str = str_replace("&rsquo;", "'", $str);
            
            $str = html_entity_decode( $str, ENT_COMPAT, 'UTF-8') ;
            
            $result[] = $str ;
            
        }
        
        return $result ;             
    }
   
}
