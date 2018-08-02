<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OpenEdition\EntityFishingClient;

/**
 * Description of EntityFishing
 *
 * @author vino
 */
class EntityFishing
{
    const TEXT = "text";
    const SHORT_TEXT =  "shortText" ;
    const TERM_VECTOR = "termVector" ;
    const ENTITIES =  "entities" ;
    const LANGUAGE = "language" ;
    const ONLY_NER = "onlyNER" ; // deprecated
    const RESULT_LANGUAGES = "resultLanguages" ; // deprecated
    const NBEST = "nbest" ;
    const SENTENCE = "sentence" ;
    const CUSTOMISATION = "customisation" ;
    const PROCESS_SENTENCE = "processSentence" ;
    const SENTENCES = "sentences" ;
    
    protected $configuration ;
    protected $logger ;
    protected $timer ;
      
    public function __construct(NerdConfiguration $configuration,  \Psr\Log\LoggerInterface $logger,  Timer $timer)
    {       
        $this->configuration = $configuration ;
        $this->logger = $logger ;          
        $this->timer = $timer ;
    }
    
    public function process($html, $language = "en"): array
    {
        if( !in_array( $language, $this->configuration->supportedLanguages ) ){
                    
            throw new EntityFishingException("document can not be processed : unsupported language ") ;
                    
        }      
        $this->timer = $this->timer->started() ;
        
        $requests = $this->createParagraphsRequests( $this->getParagraphs($html), $language ); 
                                         
        $currentEntities = [] ;
        $totalEntities = [];
                          
        foreach( $requests as $key => $request){
            
            try{
                    if( !empty( $this->configuration->timeout) && ($this->timer->seconds() > $this->configuration->timeout ) ){
                        
                        throw new EntityFishingException( "Requesting exceeded timeout" ) ;                      
                    }
                    
                    $json = \json_decode( $this->request($request)->getBody()->getContents() ) ;
                                        
                    if(!is_null($json) && isset($json->entities)){
                                                         
                        $currentEntities = $json->entities ;
                    
                        $totalEntities = array_merge($totalEntities, $currentEntities ) ;
                                                                                                                                                                                                                              
                        $this->logProgress($requests, $currentEntities, $totalEntities, $key)  ; 
                    }
                } 
                catch (\Exception $ex) { //  pb access Nerd Service or getting entities from response   
                    
                     $this->logger->warn( $ex->getMessage()) ;                                    
                }                          
        }   
        
        $this->logger->info("document disambiguated in " . (string) $this->timer  );
        
        return $totalEntities ;                            
    }
    
    protected function getParagraphs($html): array
    {
        $dom = new \DOMDocument("1.0", "UTF-8") ;
        
        try{
            $dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
        } 
        catch (\DOMException $ex) {
            
            return [] ;
        }                      
        return \array_map(function(\DOMNode $node){
           
            $str = mb_convert_encoding($node->nodeValue, "HTML-ENTITIES", "UTF-8" );           
            $str = str_replace("&rsquo;", "'", $str);   
            return html_entity_decode( $str, ENT_COMPAT, 'UTF-8') ;
        
        }, iterator_to_array( $dom->getElementsByTagName("p") ) ) ;          
    }
       
    protected function logProgress($requests = [], $currentEntities = [], $totalEntities, $index)
    {
        $str = "entities: ";
                    
        foreach( $currentEntities as $entity){
                        
            $str .= $entity->rawName . "; " ;
        }
                    
        $this->logger->debug($str ) ;
        
        $this->logger->info("entities: " . (string) (count( $currentEntities) )  . " / total: " . count( $totalEntities)
                             . " / " . (string) ( count($requests) - $index -1 ) . " requests left" );
    }
    
    
    protected function request(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $client = new \GuzzleHttp\Client();
        
        $json = \json_encode((object) $request->getAttributes() ) ;
              
        return $client->request('POST', $this->configuration->getDisambiguationEndpoint() , [
           'multipart' => [
                [
                'name'     => 'query',
                'contents' => $json,
                'headers'  => [
                    'accept' => 'application/json'
                    ]
                ]
            ]
        ]);
        
    }
         
    protected function createRequest() : \Psr\Http\Message\ServerRequestInterface
    {    
        return (new \GuzzleHttp\Psr7\ServerRequest('POST',$this->configuration->getDisambiguationEndpoint()) )
                        ->withAttribute(EntityFishing::LANGUAGE,(object) ["lang" => "en"] )
                        ->withAttribute(EntityFishing::SENTENCES, [] )                      
                        ->withAttribute(EntityFishing::NBEST,  false )
                        ->withAttribute(EntityFishing::CUSTOMISATION, "generic" )
                        ->withAttribute(EntityFishing::ENTITIES, [] )
                        ->withAttribute("mentions",["ner","wikipedia"]) ;                                 
    }
          
    
    protected function createParagraphsRequests($paragraphs, $language, $withContext = false): array
    {           
        $requests = [] ;      
        $wholeText = "";       
        $sentences = [] ;     
        $i = 0;
        
        foreach($paragraphs as $text){
            
           $offsetStart = strlen( utf8_decode($wholeText) );
                       
            $wholeText .= $text ;
            
            if(strlen($text) < $this->configuration->minimumtextSize){
                 
                 continue ;
            }                                                    
            $request =  $this->createRequest()->withAttribute(EntityFishing::TEXT, $text)
                                             ->withAttribute(EntityFishing::LANGUAGE, (object) ["lang" => $language] );         
            if($withContext){
                
                $request = $request->withAttribute(EntityFishing::SENTENCES, (object) ["offsetStart" => $offsetStart, "offsetEnd" => strlen( utf8_decode($wholeText) )  ] )
                                   ->withAttribute( EntityFishing::PROCESS_SENTENCE, array( $i++ ) )
                                   ->withAttribute(EntityFishing::TEXT, $wholeText);
            }                                                                                                                     
            $requests[] = $request ;     
            
        }
       
        return $requests ;
    }
}
class EntityFishingException extends \Exception
{
    
}

