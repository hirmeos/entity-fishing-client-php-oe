<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient ;
/**
 * Description of GenericEntityInterface
 *
 * @author vinogradov
 */
interface IGenericEntity
{
        
    public function __get($field) ;
    
    public function __set($field, $value) ;
    
}
