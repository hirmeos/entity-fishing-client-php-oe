<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OpenEdition\EntityFishingClient;

/**
 * Description of DocumentEntityInterface
 *
 * @author vino
 */
interface DocumentEntityInterface
{
    //put your code here
    public function __get($field) ;
    
    public function query(SolrQueryBuilder $query);
    
    public function getTitle();
    
    public function getUrl();
    
    public function getText();
    
    public function getType();
    
    public function getLanguages();
    
    public function getLanguage();
    
    public function getSiteName();
    
    public function getPlatformID();
    
    public function getId();
    
    public function getEntityIdentity() ;
    
    public function getHtml();
    
    public function getParagraphs();
    
    public function isAncestorOf(DocumentEntityInterface $document) ;
    
    public function setAncestors($ancestors) ;
    
    public function addAncestor(DocumentEntityInterface $ancestor) ;
    
    public function getDescents() ;
    
    public function setDescents( $descents ) ;
    
    public function isBook() ;
    
    public function isJournalIssue() ;
    
    public function isJournalRubric() ;
    
    public function getDescent() ;
}
