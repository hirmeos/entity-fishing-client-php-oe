<?php

namespace OpenEdition\EntityFishingClient ;

interface HttpClient {
    
    
   
    public function request(\Psr\Http\Message\RequestInterface $Request, \Psr\Http\Message\ResponseInterface $Response ) :  \Psr\Http\Message\ResponseInterface ;
    
    
}
class HttpException extends \Exception
{
    
}
