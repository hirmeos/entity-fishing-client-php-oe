<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OpenEdition\EntityFishingClient;

/**
 * Description of KnowledgeBaseConcept
 *
 * @author vinogradov
 */
class KnowledgeBaseConcept
{
    protected $configuration ;
    public $wikidataId;
    public $label;
    public $realClass;
    public $predictedClass;
    public $featureVector ;
       
    public function __construct(NerdConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }
    
    public function byWikidataId(string $id): KnowledgeBaseConcept
    {
        $client = new \GuzzleHttp\Client();
        
        $response = $client->get($this->configuration->uriKid(), ['query' => ['id' => $id]]);
        
        if($response->getStatusCode() !== 200){
            throw new EntityFishingException("Nerd Kid error: " . $response->getStatusCode() . " " . $response->getBody()->getContents() ) ;
        }
        
        $json = \json_decode( $response->getBody()->getContents() ) ;
        
        if(is_null($json)){
            throw new EntityFishingException("Nerd Kid error: json problem");
        }
        
        $this->label = $json->label;
        $this->wikidataId = $json->wikidataId;
        $this->predictedClass = $json->predictedClass;
        $this->realClass = $json->realClass;
        $this->featureVector = $json->featureVector ;
        return $this ;
    }
    
    public function __toString()
    {
        return "concept " . $this->label . " (" . $this->wikidataId . ") " . $this->predictedClass;
    }
}
