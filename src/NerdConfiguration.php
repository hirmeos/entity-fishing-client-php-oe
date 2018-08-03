<?php
 
namespace OpenEdition\EntityFishingClient ;

/**
 * Description of NerdConfiguration
 *
 * @author vinogradov
 */
class NerdConfiguration
{
    
    public $minimumtextSize = 11 ;
    protected $uri_fishing ;
    protected $uri_kid ;
    public $entityTypes = ["PERSON","LOCATION","PERSON_TYPE",
                            "INSTALLATION","MEDIA","AWARD","ORGANISATION",
                           "CREATION","WEBSITE","INSTITUTION","BUSINESS",
                           "EVENT","ACRONYM","NATIONAL","ANIMAL",
                           "IDENTIFIER", "ARTIFACT" ,"PERIOD", "SUBSTANCE",
                           "PLANT", "SPORT_TEAM", "CONCEPT", "CONCEPTUAL",
                            "UNKNOWN"] ;
    public $supportedLanguages = ["fr","en"];
    public $timeout ;
   
    public function __construct( $config = [] )
    {
        foreach($config as $key => $value){
            
            $this->{$key} = $value ;
        }
    }
    
    public function uriFishing(): string
    {
        if(empty($this->uri_fishing)){
            
            throw new EntityFishingException("uri_disambiguation is undefined");
        }
        return  $this->uri_fishing ;
    }
    
    public function uriKid(): string
    {
        if(empty($this->uri_kid)){
            
            throw new EntityFishingException("uri_kid is undefined");
        }
        return  $this->uri_kid ;
    }
    
}
