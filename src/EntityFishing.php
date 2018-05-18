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
    
    const MINIMUM_TEXT_SIZE = 11 ;
    
    protected $configuration ;
    protected $logger ;
    protected $httpClient ;
    protected $timer ;
      
    public function __construct(NerdConfiguration $configuration,  \Psr\Log\LoggerInterface $logger, HttpClient $client, Timer $timer)
    {
        
        $this->configuration = $configuration ;

        $this->logger = $logger ;
        
        $this->httpClient = $client ;
        
        $this->timer = $timer ;
    }
    
    public function process(NerdDisambiguation $disambiguation, DocumentEntityInterface $document)
    {
        $this->validateDocument( $document ) ;
         
        $this->timer->start() ;
        
        $requests = $this->createRequests( $document ); 
                                         
        $currentEntities = [] ;
        
        $this->logger->info("Interrogating Nerd API for " . $document->getUrl() . " ..." );
               
        foreach($requests as $key => $request){
            
            try{
                    $this->checkRunningTime() ; // stops fishing if timeout
                    
                    //$this->logRequest($request, $key) ;
                    
                    $currentEntities = $this->fishParagraph($request, $disambiguation) ;
                                                                                                                                                                                              
                    $disambiguation->updateProgress( $key + 1, $this->timer->getElapsed( false ) ) ;
                                      
                    $this->logProgress($disambiguation, $requests, $currentEntities, $key)   ;                                                                              
                                                                                                                                                             
                } 
                catch (HttpException $ex) { //  pb access Nerd Service or getting entities from response   
                    
                     $this->logger->warn( "[untreatable text: ] " . $request->getText() ) ;
                                     
                }
                catch(JsonException $ex){
                    
                }                           
        }
               
        $this->logger->info("document disambiguated in " . $this->timer->getElapsed() . " secs" );
                                    
        
        $disambiguation->setModificationDateToNow() ;
            
        return $disambiguation ;
    }
    
    protected function fishParagraph(NerdRequest $request, NerdDisambiguation $disambiguation)
    {
        $Response = $this->request( $request ) ;
                    
        $disambiguation->appendEntities( $Response->read( EntityFishing::ENTITIES ) );
        
        return $Response->read( EntityFishing::ENTITIES ) ;
    }
    
    protected function fishParagraphInContext(NerdRequest $request, NerdDisambiguation $disambiguation, $previousEntities)
    {
        $Response = $this->request( $request->withAttribute( EntityFishing::ENTITIES, $previousEntities )  );
        
        $disambiguation->setData( $Response->read( EntityFishing::ENTITIES ) ) ;
        
        return array_slice( $Response->read( EntityFishing::ENTITIES ), count($previousEntities) ) ;
    }
    
    protected function logProgress(NerdDisambiguation $disambiguation, $requests = [], $currentEntities = [], $index)
    {
        $str = "entities: ";
                    
        foreach( $currentEntities as $entity){
                        
            $str .= $entity->rawName . "; " ;
        }
                    
        $this->logger->debug($str ) ;
        
        $this->logger->info("entities: "
                             . (string) (count( $currentEntities) )  
                             . " / total: " . count( $disambiguation->getEntities() )
                             . " / " . (string) ( count($requests) - $index -1 ) . " requests left"
                             . "  / duration: " . round($this->timer->getElapsed( false ),1 ) 
                             . " /total size: ". $disambiguation->getDataSize()
                                         
                             );
    }
    
    protected function logRequest(NerdRequest $request, $key)
    {
        $this->logger->debug( "[Paragraph " . (string) ($key+1) . "] " . $request->getText() ) ;
    }
    
    protected function checkRunningTime()
    {
        if( $this->configuration->has("timeout") && ($this->timer->getElapsed( false ) > $this->configuration->__get("timeout") ) ){
                        
            throw new EntityFishingException( "Requesting exceeded timeout" ) ;
                        
        }
    }
    
    protected function createRequests(DocumentEntityInterface $document)
    {
        try{
            
             $requests = $this->createParagraphsRequests($document ) ;  // dividing in paragraphs
                     
        } 
        catch (EntityFishingException $ex) {
                        
            $requests = array( $this->createWholeTextRequest( $document  ) ) ;   // taking whole text
                                      
        }

        return $requests ;
    }
    
    protected function request(ServerRequestInterface $Request)
    {
  
        $Response = $this->httpClient->request( $Request, new JsonResponse() );
        
        if( $Response->getStatusCode() != StatusCode::OK ){
            
            $this->logger->warn("Nerd API returned " . $Response->getStatusCode() . " status code " );
            
            throw new HttpException( $Response->getBody(), $Response->getStatusCode() );
        }
        
        
        return $Response ;
    }
    
    protected function validateDocument(DocumentEntityInterface $document)
    {
        try{
                          
            if( !in_array( $document->getLanguage(), $this->configuration->getSupportedLanguages() ) ){
                    
                throw new EntityFishingException("document " . $document->getUrl() . " can not be processed : unsupported language " . $document->getLanguage() ) ;
                    
            }
           
        } catch (\Exception $ex) {
            
            // if no language it is English by default in request
        }
        
        try{
            
            $document->getText();
            
        } catch (\Exception $ex) {
            
            throw new EntityFishingException("document " . $document->getUrl() . " can not be processed : no text " ) ;
        }
        
    }
    
    
    protected function createRequest()
    {
        $Request = new NerdRequest();
        
        return $Request->withAttribute(EntityFishing::LANGUAGE,(object) array("lang" => "en" ) )
                        ->withAttribute(EntityFishing::SENTENCES, array() )                      
                        ->withAttribute(EntityFishing::NBEST,  false )
                        ->withAttribute(EntityFishing::CUSTOMISATION, "generic" )
                        ->withAttribute(EntityFishing::ENTITIES, array() )
                        ->withAttribute("mentions",["ner","wikipedia"])
                        ->withHeader("Content-Type", "multipart/form-data; boundary=". $Request->getBoundary() . "")
                        ->withMethod("post") 
               ;
    }
      
    
    protected function createWholeTextRequest(DocumentEntityInterface $entity)
    {
       
        try{
             if(strlen($entity->getText() ) < self::MINIMUM_TEXT_SIZE){
            
                throw new EntityFishingException("text " . $entity->getText() ." is too short");
            }           
            
            $Request =  $this->createRequest()->withUri( new Uri( $this->configuration->getDisambiguationEndpoint() ) )
                                              ->withAttribute(EntityFishing::TEXT, $entity->getText() );
                                            
            
        } 
        catch (\Exception $ex) {
            
            throw new EntityFishingException($ex->getMessage() );
            
        }
                       
        try{
                        
            $Request = $Request->withAttribute(EntityFishing::LANGUAGE, (object) array("lang" => $entity->getLanguage() )  ) ;
            
        } 
        catch (\Exception $ex) {
        }
        
        return $Request ;     
        
    }
    
    protected function createParagraphsRequests(DocumentEntityInterface $entity, $withContext = false)
    {
      
        try{
            
            $paragraphs = $entity->getParagraphs() ;
            
        } 
        catch (\Exception $ex) {
            
             throw new EntityFishingException("getting paragraphs failed : " . $ex->getMessage() );
             
        }
        
        $requests = array() ;
       
        $wholeText = "";
        
        $sentences = array() ;
        
        $i = 0;
        
        foreach($paragraphs as $text){
            
            $offsetStart = strlen( utf8_decode($wholeText) );
                       
            $wholeText .= $text ;
            
            if(strlen($text) < self::MINIMUM_TEXT_SIZE){
                 
                 continue ;
            }          
                       
            $sentences[] = (object) array("offsetStart" => $offsetStart,
                                          "offsetEnd" => strlen( utf8_decode($wholeText) )  
                                        );
                     
             
            $Request =  $this->createRequest()->withUri( new Uri( $this->configuration->getDisambiguationEndpoint() ) )
                                             ->withAttribute(EntityFishing::TEXT, $text);
            
            if($withContext){
                
                $Request = $Request->withAttribute(EntityFishing::SENTENCES, $sentences )
                                   ->withAttribute( EntityFishing::PROCESS_SENTENCE, array( $i++ ) )
                                   ->withAttribute(EntityFishing::TEXT, $wholeText);
            }
                                                                                                             
            try{
                                          
                  $Request = $Request->withAttribute(EntityFishing::LANGUAGE, (object) array("lang" => $entity->getLanguage() )  ) ;
            
                } 
                catch (\Exception $ex) {
                }
              
            $requests[] = $Request ;
            
        }
        
        if(count($requests) == 0){
            
            throw new EntityFishingException(" document can not be divided in paragraphs " );
            
        }
       
        return $requests ;
    }
}
class EntityFishingException extends \Exception
{
    
}

