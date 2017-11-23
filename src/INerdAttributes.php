<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace EntityFishingClient ;
/**
 * Description of INerdAttributes
 *
 * @author vinogradov
 */
interface INerdAttributes
{
    //put your code here
    const TEXT = "text";
    const SHORT_TEXT =  "shortText" ;
    const TERM_VECTOR = "termVector" ;
    const ENTITIES =  "entities" ;
    const LANGUAGES = "languages" ;
    const ONLY_NER = "onlyNER" ;
    const RESULT_LANGUAGES = "resultLanguages" ;
    const NBEST = "nbest" ;
    const SENTENCE = "sentence" ;
    const CUSTOMISATION = "customisation" ;
    const PROCESS_SENTENCE = "processSentence" ;
    const SENTENCES = "sentences" ;
}
