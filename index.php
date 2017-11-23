<?php


namespace EntityFishingClient ;


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require 'vendor/autoload.php';

$document = (new EntityFactory() )->fromCliArguments($argv) ; 

$client = new NerdClient(new NerdConfiguration() , new NerdDisambiguationRepository(), new Logger() );

$disambiguation = $client->disambiguate(new NerdDisambiguation(), $document, new NerdPresenter() ) ;


