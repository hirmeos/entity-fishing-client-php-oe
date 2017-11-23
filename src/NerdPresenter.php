<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient ;

/**
 * Description of NerdDecorator
 *
 * @author vinogradov
 */
class NerdPresenter
{
       
    public function render(IGenericEntity $entity)
    {
        if(is_a($entity, NerdDisambiguation::class)){
            
            $this->renderDisambiguation($entity) ;
            
        }
    }
    
    public function renderDisambiguation(NerdDisambiguation $disambiguation)
    {
        print_r($disambiguation->getEntities()) ;
       
    }
}
