<?php


/**
 * @author  <asudau@uos.de>
 *
 */
class ZertifikatsprogrammExamConfirmed extends \SimpleORMap
{

    public $errors = array();

    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'zertifikatsprogramm_exam_confirmed';
        parent::configure($config);
    }

}

