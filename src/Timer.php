<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace OpenEdition\EntityFishingClient;
/**
 * Description of Timer
 *
 * @author vino
 */
class Timer
{

    protected $start ;
    
    public function __construct()
    {
        $this->start = microtime(true) ;     
    }
    
    public function started(): Timer
    {
        $new = clone $this ;
        $new->start = microtime(true) ;
        return $new;
    }
    
    public function seconds()
    {
        return round($this->elapsed(),2) ;
    }
    
    public function __toString()
    {
        $secs = $this->elapsed() ;
        $hours = floor($secs / 3600) ;
        
        $secs -= $hours * 3600;
        $mins = floor($secs / 60) ;
        
        $secs -= $mins * 60;
        
        $str = "";
        if($hours > 0){
            $str .= $hours . " hours ";
        }    
        if($mins > 0){
            $str .= $mins . " mins ";
        }      
        
        return $str . round($secs,2) . " secs" ;      
    }
    
    public function elapsed()
    {
        return microtime(true) - $this->start ;
    }
    
}
