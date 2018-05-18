 <?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace OpenEdition\EntityFishingClient ;


/**
 * Description of NerdConfiguration
 *
 * @author vinogradov
 */
class NerdConfiguration
{
    
    public function __construct()
    {
        
    }
    
    public function getDisambiguationEndpoint()
    {
        return  $this->getBaseUri() . "nerd/disambiguate" ;
    }
    
    public function getKnowledgeBaseEndpoint()
    {
        return  $this->getBaseUri() . "nerd/service/kb/concept" ;
    }
    
    public function getEntityTypes()
    {
        return array("PERSON","LOCATION","PERSON_TYPE",
                    "INSTALLATION","MEDIA","AWARD","ORGANISATION",
                    "CREATION","WEBSITE","INSTITUTION","BUSINESS",
                    "EVENT","ACRONYM","NATIONAL","ANIMAL",
                    "IDENTIFIER", "ARTIFACT" ,"PERIOD", "SUBSTANCE",
                    "PLANT", "SPORT_TEAM", "CONCEPT", "CONCEPTUAL",
                    "UNKNOWN"
                    );
    }
    
    private function getBaseUri()
    {
        
    }
    
}
