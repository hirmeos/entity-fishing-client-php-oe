<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace EntityFishingClient ;

use Psr\Http\Message\RequestInterface ;
use GuzzleHttp\Psr7\Response ;

/**
 * Description of NerdClient
 *
 * @author vinogradov
 */
class NerdClient
{
    protected $repository;
    protected $configuration ;
    protected $logger ;
      
    public function __construct(NerdConfiguration $configuration, NerdDisambiguationRepository $repository, Logger $logger)
    {
        
        $this->configuration = $configuration ;
        $this->repository = $repository ;
        $this->logger = $logger ;
        
    }
        
    public function request(NerdRequest $Request)
    {
        
        $Response = (new HttpClient() )->request( $Request,
                                                 new Response()
                                                );
        
        if( $Response->getStatusCode() != StatusCode::OK ){
           
            throw new UnexpectedResponseException( $Response->getBody(),
                                                   $Response->getStatusCode()
                                                );
        }
        
        
        return $Response ;
    }
    
    
    public function getDisambiguationRequests(IDocumentEntity $entity)
    {
        try{
             $requests = (new NerdRequestFactory() )->createDisambiguateParagraphsRequests($entity, $this->configuration->getDisambiguationEndpoint() ) ;  // dividing in paragraphs
        } 
        catch (NerdRequestFactoryException $ex) {
            
            $this->logger->debug(" no paragraphs, taking full text " );
            
            try{
                $requests = array( (new NerdRequestFactory() )->createDisambiguateRequest($entity, $this->configuration->getDisambiguationEndpoint()  ) ) ;   // taking whole text
                
            } 
            catch (NerdRequestFactoryException $ex) {
                
                throw new \RuntimeException(" untreatable entity " .$entity->getTitle() . $ex->getMessage() );
                
            }
            
        }

        return $requests ;
    }
    
    public function disambiguate(NerdDisambiguation $disambiguation, IDocumentEntity $document, NerdPresenter $presenter )
    {
        
        $requests = $this->getDisambiguationRequests($document);
        
        $currentEntities = $disambiguation->getEntities() ;
        
        $offset = $disambiguation->__get("requests_done") ; // if previous disambiguation hasn't completed
                        
        foreach(array_slice($requests, $offset) as $key => $request){
            
            try{
                   
                    $this->logger->debug("Request N " . $offset + $key . " of " . count($requests) );
                                                                                                           
                    $Response = $this->request( $request->withAttribute( INerdAttributes::ENTITIES, $currentEntities ) );
                                      
                    $currentEntities = (new NerdInputJsonAdapter( $Response->getBody()->getContents() ) )->getEntities() ;
                                       
                    $this->logger->debug("current entities: " . count( $currentEntities ) );
                                                                  
                    $disambiguation->setEntities( \json_encode( $currentEntities ) ); 
                                                 
                    $disambiguation->__set("requests_done" , $offset + $key + 1 );   
                                 
                    $this->repository->update( $disambiguation );
                        
                    $presenter->render( $disambiguation ) ;
                                                           
                } 
                catch (UnexpectedResponseException $ex) { //  pb access Nerd Service or getting entities from response
                    
                    $this->logger->debug( $ex->getMessage() ) ;
                  
                }
                catch(InvalidDataException $ex){
                    
                }
                             
        }
                            
        $this->repository->update( $disambiguation );
        
        return $disambiguation ;
    }
    
   
       
}
