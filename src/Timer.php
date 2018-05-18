<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Timer
 *
 * @author vino
 */
interface Timer
{
    public function start();
    
    
    public function end();
    
    
    public function stop();
    
    
    public function now();
    
    
    public function getElapsed( $stop = true);
    
    
    
    
   
}
