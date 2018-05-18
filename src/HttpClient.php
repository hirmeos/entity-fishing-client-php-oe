<?php

namespace OpenEdition\EntityFishingClient ;

use Psr\Http\Message\RequestInterface ;
use Psr\Http\Message\ResponseInterface ;
use GuzzleHttp\Psr7\BufferStream ;

class HttpClient {
    
    private static $defaultOptions = array(CURLOPT_RETURNTRANSFER => true,
                                            CURLOPT_TIMEOUT => 150,
                                            CURLOPT_HEADER => true,
                                            CURLOPT_FOLLOWLOCATION => false);
    
    private $curlOptions = array();
    
    public function __construct( $options = array() )
    {
        $this->curlOptions = $options + self::$defaultOptions;
    }
    /********************  Request implementing PSR-7   *********************/
    /**
     * 
     * @param object (implements PSR-7 Request Interface) $Request
     * @param object (implements PSR-7 Response interface) $Response
     * @return object (implements PSR-7 Response interface)
     */
    public function request(RequestInterface $Request, ResponseInterface $Response )
    {
             
        $headers = array();
        
        foreach($Request->getHeaders() as $headerName => $headerArray){
            
            foreach($headerArray as $headerLine ){
                
                $headers[] = $headerName. ": ". $headerLine;
                
            }
            
        }
        
        $uri = $Request->getUri();
        
        if(count($Request->getQueryParams() ) > 0){
            
            parse_str($uri->getQuery(), $params);  
            $params = array_merge($params, $Request->getQueryParams() );
            $uri = $uri->withQuery( http_build_query( $params) ) ;
            
        }
               
        $body = $Request->getBody();
        
        $options = array(
                        CURLOPT_URL => (string) $uri,
                        CURLOPT_HTTPHEADER => $headers
                        );
        
        $options = $options + $this->curlOptions; 
        
        if(strtolower($Request->getMethod()) == "post"){
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = (string) $body;
        }
        if(strtolower($Request->getMethod()) == "put"){
            $options[CURLOPT_CUSTOMREQUEST] = "PUT";
            $options[CURLOPT_POSTFIELDS] = (string) $body;
        }
        if(strtolower($Request->getMethod()) == "delete"){
           $options[CURLOPT_CUSTOMREQUEST] = "DELETE";
        }
         
        $ch = curl_init();
        curl_setopt_array($ch, $options); 
             
        $result = curl_exec($ch);
        
        if($result === false){
            $error = "URL " . (string) $uri. " unreachable ";
            if( curl_error($ch) ){
                $error .= curl_error($ch);
            }
            curl_close($ch);
            throw new HttpClientException( $error );
        }
        
        $statusCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerContent = substr($result, 0, $headerSize);
        
        $responseBody = new BufferStream();     
        $responseBody->write( substr($result, $headerSize) );
        
        curl_close($ch);
        
        $Response = $Response->withStatus(  $statusCode  ) ;
        
        
        foreach(self::parseHeaders($headerContent)[0] as $headerName => $headerValue ){
            
            $Response = $Response->withHeader($headerName, $headerValue );
            
        }
        
        return $Response->withBody( $responseBody );
                       
    }
   
      
    public static function parseHeaders($headerContent)
    {
        $headers = array();
        
        $lineBreak = "\r\n";
        $arrRequests = explode($lineBreak.$lineBreak, $headerContent);
        
        for ($index = 0; $index < count($arrRequests); $index++) {
            foreach (explode($lineBreak, $arrRequests[$index]) as $i => $line){
                if ($i === 0){
                    $headers[$index]['http_code'] = $line;
                }
            else{
                    list ($key, $value) = explode(': ', $line);
                    $headers[$index][$key] = $value;
                }
            }
        }
        return $headers;
    }
    
    
    public static function getHeaderValue($headerContent,$headerName)
    {
        $headers = self::parseHeaders($headerContent);
        foreach($headers as $header){
            if(isset($header[$headerName])){
                return $header[$headerName];
            }
        }
        return false;
    }
    
}
class HttpException extends \Exception
{
    
}
