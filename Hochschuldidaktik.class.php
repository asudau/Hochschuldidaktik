<?php

/**
 * Doktorandenverwaltung.class.php
 *
 * ...
 *
 * @author  Annelene Sudau <asudau@uos.de>
 * @version 0.1a
 */


class Hochschuldidaktik extends StudipPlugin implements SystemPlugin 
{

    const HOCHSCHULDIDAKTIK_ROLE = 'Hochschuldidaktik';
    //const STUDY_AREA_ID = '5b73e28644a3e259a6e0bc1e1499773c';
    const STUDY_AREA_ID = '7d6a75b2d82b2c06cbe86bd646304ce2';
    
    public function __construct()
    {
        parent::__construct();
        global $perm;

        if(RolePersistence::isAssignedRole($GLOBALS['user']->user_id,
                                                            self::HOCHSCHULDIDAKTIK_ROLE)){
            $navigation = new Navigation('Zertifikatsprogramm');
            $navigation->setImage(Icon::create('edit', 'navigation'));
            $navigation->setURL(PluginEngine::getURL($this, array(), 'index'));
            
            $item = new Navigation(_('Übersicht'), PluginEngine::getURL($this, array(), 'index'));
            $navigation->addSubNavigation('index', $item);
            
            $item = new Navigation(_('Teilnehmende'), PluginEngine::getURL($this, array(), 'index/members'));
            $navigation->addSubNavigation('members', $item);
            
            Navigation::addItem('tools/hochschuldidaktik', $navigation);  
        }    
    }

    public function initialize ()
    {
        
    }

    public function perform($unconsumed_path)
    {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
        
    }

    private function setupAutoload()
    {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }
}
