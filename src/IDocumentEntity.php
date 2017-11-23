<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient ;
/**
 * Description of IDocumentEntity
 *
 * @author vinogradov
 */
interface IDocumentEntity
{
       
    public function getText();
    
    public function getLanguages();
      
    public function getHtml();
    
    public function getParagraphs();
       
    
}
