<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace OpenEdition\EntityFishingClient;
/**
 * Description of NerdDisambiguation
 *
 * @author vinogradov
 */
interface NerdDisambiguation 
{
   
    
    public function getEntities() ;
    
    
    public function setEntities( $entities ) ;
    
    
    public function updateProgress($requestsDone, $processTime) ;
    
    
    
    
}
