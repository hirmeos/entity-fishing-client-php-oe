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
    
    public function entitiesFromHtml(string $html, string $language = "en", int $offset = 0, int $length = 0): array
    {
        if( !in_array( $language, $this->configuration->supportedLanguages ) ){                   
            throw new EntityFishingException("html can not be processed : unsupported language ") ;                   
        }      
        $this->timer = $this->timer->started() ;         
        $paragraphs = $this->paragraphsFromHtml($html) ;       
        if( $length>0 ){          
            $paragraphs = array_slice($paragraphs, $offset, $length) ;
        }      
        $requests = $this->createParagraphsRequests( $paragraphs, $language );                                       
        $entities = [];
        $client = new \GuzzleHttp\Client();                          
        foreach( $requests as $key => $request){       
            if( !empty( $this->configuration->timeout) && ($this->timer->seconds() > $this->configuration->timeout ) ){                  
                throw new EntityFishingException( "Requesting exceeded timeout" ) ;                      
            }                
            $response = $client->request('POST', $this->configuration->uriFishing() , 
                                         ['multipart' => [['name' => 'query',
                                                           'contents' => \json_encode( (object) $request->getAttributes() ),
                                                           'headers'  => ['accept' => 'application/json']
                                                         ]]
                                        ]);
            if($response->getStatusCode() !== 200){               
                $this->logger->error( $response->getBody()->getContents() ) ;
                continue ;
            }               
            $json = \json_decode( $response->getBody()->getContents() ) ;                                        
            if(!is_null($json) && isset($json->entities)){
                                                                       
                $entities = array_merge($entities, $json->entities ) ;                                                                                                                                                                                                
                $this->logProgress($json->entities, $entities, count($requests) - $key -1)  ; 
            }                               
        }        
        $this->logger->info("html processed in " . (string) $this->timer  );       
        return $entities ;                            
    }
    
    public function entitiesFromText(string $text,string $language): array
    {
        $request =  $this->createRequest()->withAttribute(EntityFishing::TEXT, $text)
                                          ->withAttribute(EntityFishing::LANGUAGE, (object) ["lang" => $language] );
        $response = (new \GuzzleHttp\Client() )->request('POST', $this->configuration->uriFishing() , 
                                         ['multipart' => [['name' => 'query',
                                                           'contents' => \json_encode( (object) $request->getAttributes() ),
                                                           'headers'  => ['accept' => 'application/json']
                                                         ]]
                                        ]);
        if($response->getStatusCode() !== 200){ 
            throw new EntityFishingException($response->getStatusCode() . " " .$response->getBody()->getContents() ) ;
        }
        $json = \json_decode( $response->getBody()->getContents() ) ;                                        
        if(!is_null($json) && isset($json->entities)){
             return   $json->entities ;
        }
        return [] ;
    }
    
    public function paragraphsFromHtml(string $html): array
    {
        $dom = new \DOMDocument("1.0", "UTF-8") ;       
        try{
            $dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
        } 
        catch (\DOMException $ex) {        
            return [] ;
        }                      
        return \array_map(function(\DOMNode $node){                    
            $str = str_replace("&rsquo;", "'", mb_convert_encoding($node->nodeValue, "HTML-ENTITIES", "UTF-8" ) );   
            return html_entity_decode( $str, ENT_COMPAT, 'UTF-8') ;       
        }, iterator_to_array( $dom->getElementsByTagName("p") ) ) ;          
    }
       
    protected function logProgress(array $currentEntities , array $allEntities, int $remainingRequests)
    {                   
        $this->logger->debug( implode(",", \array_map( function($entity){return $entity->rawName;}, $currentEntities) ) ) ;       
        $this->logger->info("entities: " . (string) (count( $currentEntities) )  . " / total: " . count( $allEntities)
                             . " / " . (string) $remainingRequests . " requests left " . (string) $this->timer );
    }
           
    protected function createRequest() : \Psr\Http\Message\ServerRequestInterface
    {    
        return (new \GuzzleHttp\Psr7\ServerRequest('POST',$this->configuration->uriFishing()) )
                        ->withAttribute(EntityFishing::LANGUAGE,(object) ["lang" => "en"] )
                        ->withAttribute(EntityFishing::SENTENCES, [] )                      
                        ->withAttribute(EntityFishing::NBEST,  false )
                        ->withAttribute(EntityFishing::CUSTOMISATION, "generic" )
                        ->withAttribute(EntityFishing::ENTITIES, [] )
                        ->withAttribute("mentions",["ner","wikipedia"]) ;                                 
    }
              
    protected function createParagraphsRequests(array $paragraphs, string $language, bool $withContext = false): array
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

