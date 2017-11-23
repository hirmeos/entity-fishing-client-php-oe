<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient ;
/**
 * Description of EmptyFieldException
 *
 * @author vinogradov
 */
class EmptyFieldException extends \Exception
{
    //put your code here
    public function __construct($message, $code = 0, \Exception $previous = null) {
    
    parent::__construct(__CLASS__.$message, $code, $previous);
  }

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
}
