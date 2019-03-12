<?php
class IndexController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Zertifikatsprogramm - Übersicht"));

        //PageLayout::addStylesheet($this->plugin->getPluginURL().'/assets/style.css');

//        $sidebar = Sidebar::Get();
//
//        $navcreate = new ActionsWidget();
//        $navcreate->addLink(_('Neuer Workshop'),
//                              $this->url_for('index/new'),
//                              Icon::create('seminar+add', 'clickable'))->asDialog('size=big');
//        
//        $sidebar->addWidget($navcreate);
        
    }

    public function index_action()
    {
        Navigation::activateItem('tools/hochschuldidaktik/index');
        
        
        $study_area_zertifikate = StudipStudyArea::find(Hochschuldidaktik::STUDY_AREA_ID );
        $workshops = $study_area_zertifikate->courses;
        
        foreach($workshops as $course){
            if ($course->dates[0]){
                $workshops_with_date[$course->dates[0]->date] = $course;
            } else {
                $this->workshops_without_date[] = $course;
            }
        }
        krsort($workshops_with_date);
        
        $this->workshops = [];
        foreach($workshops_with_date as $date => $course){
            array_push($this->workshops, $course);
        }
        $this->workshops = array_merge($this->workshops, $this->workshops_without_date);
    }

    public function showactive_action(){
        $this->render_nothing();
    }
    
    
    public function edit_action($entry_id)
    {
        $this->entry = DoktorandenEntry::findOneBySQL('id = ' . $entry_id);
        if(RolePersistence::isAssignedRole($GLOBALS['user']->user_id,
                                                            Doktorandenverwaltung::DOKTORANDENVERWALTUNG_ADMIN_ROLE)){
            $this->groupedFields = DoktorandenEntry::getGroupedFieldsForAdmin();
        } else
            $this->groupedFields = DoktorandenEntry::getGroupedFields();
    }
    
    public function faq_action()
    {
        Navigation::activateItem('tools/doktorandenverwaltung/faq');
    }

    public function new_action()
    {
        $this->entry = new DoktorandenEntry();
        //$this->entry->store();
        //$this->new = true;
        if(RolePersistence::isAssignedRole($GLOBALS['user']->user_id,
                                                            Doktorandenverwaltung::DOKTORANDENVERWALTUNG_ADMIN_ROLE)){
            $this->groupedFields = DoktorandenEntry::getGroupedFieldsForAdmin();
        } else
            $this->groupedFields = DoktorandenEntry::getGroupedFields();
    }

    public function delete_action($entry_id){
        if(RolePersistence::isAssignedRole($GLOBALS['user']->user_id,
                                                            Doktorandenverwaltung::DOKTORANDENVERWALTUNG_ADMIN_ROLE)){
            $entry = DoktorandenEntry::findOneBySQL('id = ' . $entry_id);
            if ($entry){
                $message = MessageBox::success(_('Eintrag wurde gelöscht: ' . $entry->vorname . ' ' . $entry->nachname));
                $entry->delete();
            } else $message = MessageBox::success(_('Kein Eintrag mit dieser ID vorhanden'));
            PageLayout::postMessage($message);
        }
        $this->redirect('index');
    }
    
    public function save_action($entry_id){

        if ($entry_id){
            $entry = DoktorandenEntry::findOneBySQL('id = ' . $entry_id);
        } else {
            $entry = new DoktorandenEntry();
        }
        $groupedFields = DoktorandenEntry::getGroupedFields();
        
        if($entry){
            foreach ($groupedFields as $group){
                foreach ($group['entries'] as $field_entry){
                    $field = $field_entry->id;
                    //if(Request::option($field)){
                        if (strpos($field, 'jahr') !== false){
                            if (Request::get($field)){
                                $input = Request::int($field);
                                if($input>1000 && $input<2100){
                                    $entry->$field = Request::int($field);
                                } else {
                                    $message = MessageBox::error(_('Falsches Datumsformat: ' . $field_entry->title . ' wurde nicht übernommen'));
                                    PageLayout::postMessage($message);
                                }
                            } 
                        } if ($field == 'geburtstag'){
                            if (Request::get($field)){
                                if($this->validateDate(Request::get($field))){
                                    $entry->$field = htmlReady(Request::get($field));
                                } else {
                                    $message = MessageBox::error(_('Falsches Datumsformat: ' . $field_entry->title . ' wurde nicht übernommen'));
                                    PageLayout::postMessage($message);
                                }
                            } 
                        }
                        else {
                                $entry->$field = Request::get($field);
                            }
                    //}
                }
            }
            //$entry->store();

            //anzahl required fields aktualisieren
            $filled = 0;
            $req_fields = $entry->requiredFields();
            foreach($req_fields as $field_id){
                if ($entry->isValueSet($field_id)){
                    $filled ++;
                } 
            }
            $entry->complete_progress = $filled;
            $entry->number_required_fields = sizeof($req_fields);
            
            if ($entry->store() !== false) {
                $messagetext = 'Die Änderungen wurden übernommen.';
                if ($entry->complete_progress < $entry->number_required_fields){
                    $number_missing_fields = $entry->number_required_fields - $entry->complete_progress;
                    $messagetext .= ' Für diesen Eintrag fehlen noch ' . $number_missing_fields . ' Angaben';
                }
                $message = MessageBox::success($messagetext);
                PageLayout::postMessage($message);
            } 
                
        } else {

            $message = MessageBox::success(_('Kein Eintrag mit dieser ID vorhanden'));
            PageLayout::postMessage($message);
            
        }

        //$this->response->add_header('X-Dialog-Close', '1');
        //$this->render_nothing();
        $this->redirect('index');
          
    }
    
    //Bericht für 2018 erstellen
    public function export_action()
    {
        if(RolePersistence::isAssignedRole($GLOBALS['user']->user_id,
                                                            Doktorandenverwaltung::DOKTORANDENVERWALTUNG_ADMIN_ROLE)){
            $search_query = array();
            //noch kein enddatum
            $search_query[] = '`promotionsende_jahr` IS NULL';
            $search_query[] = '`promotionsende_jahr` = \'\'';
            //oder ab 01.12.2017
            $search_query[] = '`promotionsende_jahr` = 2018';
            $search_query[] = '(`promotionsende_jahr` = 2017 AND `promotionsende_monat` = 12 AND `berichtet` != 2017 )';
            $query = implode(" OR ",$search_query);

            $doktoranden_entries = DoktorandenEntry::findBySQL($query);

            $export_fields = DoktorandenFields::getExportFieldsArray();

            $header = array();
            $export = array();

            foreach ($export_fields as $field){
                $header[] = $field->export_name;
            }

            $export[] = $header;

            $i = 0;
            foreach ($doktoranden_entries as $entry){
                $export[] = self::handleSingleRow($entry, $export_fields, $i);
                $i++;
            }

            $this->render_csv($export, 'bericht_promovierendendaten.csv');

        }
    }
    
    public function full_export_action(){
        if(RolePersistence::isAssignedRole($GLOBALS['user']->user_id,
                                                            Doktorandenverwaltung::DOKTORANDENVERWALTUNG_ADMIN_ROLE)){
            $doktoranden_entries = DoktorandenEntry::findBySQL('true');

            $export_fields = DoktorandenFields::getFullExportFieldsArray();

            $header = array();
            $export = array();

            foreach ($export_fields as $field){
                $header[] = $field->id;
                if ($field->export_name){
                    $header[] = $field->export_name;
                }
            }

            $export[] = $header;

            foreach ($doktoranden_entries as $entry){
                $export[] = self::handleFullSingleRow($entry, $export_fields);
            }

            $this->render_csv($export, 'bericht_promovierendendaten.csv');
        }
    }
    public function export_user_action()
    {
        $search_query = array();
        //noch kein enddatum
        $search_query[] = '`promotionsende_jahr` IS NULL';
        $search_query[] = '`promotionsende_jahr` = \'\'';
        //oder ab 01.12.2017
        $search_query[] = '`promotionsende_jahr` = 2018';
        $search_query[] = '(`promotionsende_jahr` = 2017 AND `promotionsende_monat` = 12 AND `berichtet` != 2017 )';
        $query = implode(" OR ",$search_query);
        
        $this->faecher = $this->getFaecherIDsForUser();

        if ($this->faecher){
            $doktoranden_entries = DoktorandenEntry::findBySQL("(" . $query . ") AND promotionsfach IN ('" . implode($this->faecher, '\' ,\'') . "')" ); 
        } else {
            $doktoranden_entries = DoktorandenEntry::findBySQL($query);
        }

        $grouped_fields = DoktorandenEntry::getGroupedFields();

        $header = array();
        $export = array();
        
        foreach ($grouped_fields as $group){
            foreach ($group['entries'] as $field){
                $header[] = $field->title;
            }
        }

        $export[] = $header;

        foreach ($doktoranden_entries as $entry){
            $export[] = self::handleUserSingleRow($entry, $grouped_fields);
        }

        $this->render_csv($export, 'promovierendendaten.csv');

    }
    
    static function handleSingleRow($entry, $fields, $number)
    {
        $rowData = array();
        foreach($fields as $field){
            $field_id = $field->id;
            if($field_id == 'paginiernummer'){
                $rowData[] = str_pad($number, 6, '0', STR_PAD_LEFT);
            } else if($field_id == 'promotionsende_monat' ){
                if(($entry->art_reg_prom == '3' || $entry->art_reg_prom == '2') && !$entry->$field_id){
                    $rowData[] = '12';
                } else $rowData[] = $entry->$field_id;
                
            }  else if($field_id == 'promotionsende_jahr' ){
                if(($entry->art_reg_prom == '3' || $entry->art_reg_prom == '2') && !$entry->$field_id ){
                    $rowData[] =  '2018';
                } else $rowData[] = $entry->$field_id;
                
            } else {
            //get related astat_bund val of $entry->$field
                if ($field->getValueAstatByKey($entry->$field_id)){
                    $rowData[] = $field->getValueAstatByKey($entry->$field_id);
                } else if ($entry->$field_id != 'NULL'){
                    $rowData[] = $entry->$field_id;
                } else 
                   $rowData[] = ''; 
            }
        }

        return $rowData;
    }
    
    static function handleFullSingleRow($entry, $fields)
    {
        $rowData = array();
        foreach($fields as $field){
            
            $field_id = $field->id;
            //intern db-value
            $rowData[] = $entry->$field_id;
            //if exportfield: get related astat_bund val of $entry->$field
            if ($field->export_name){
                if ($field->getValueAstatByKey($entry->$field_id)){
                    $rowData[] = $field->getValueAstatByKey($entry->$field_id);
                } else if ($entry->$field_id != 'NULL'){
                    $rowData[] = $entry->$field_id;
                } else 
                    $rowData[] = ''; 
            }
        }

        return $rowData;
    }
    
    static function handleUserSingleRow($entry, $grouped_fields)
    {
        $rowData = array();
        foreach ($grouped_fields as $group){
            foreach($group['entries'] as $field){
                $field_id = $field->id;
                //intern db-value
                if ($field->getValueTextByKey($entry->$field_id)){
                    $rowData[] = $field->getValueTextByKey($entry->$field_id);
                } else if ($entry->$field_id != 'NULL'){
                    $rowData[] = $entry->$field_id;
                } else 
                    $rowData[] = ''; 
            }
        }

        return $rowData;
    }

    private function getFaecherIDsForUser(){
        //zugehörige Fächer für aktuellen User
        
        $this->inst_id = RolePersistence::getAssignedRoleInstitutes($GLOBALS['user']->user_id, $this->role_id);
        if ($this->inst_id[1]){
            $stmt = DBManager::get()->prepare("SELECT fach_id FROM mvv_fach_inst WHERE Institut_id IN (?)");
            $stmt->execute(array($this->inst_id));
            $faecher = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            $faecher_array = array();
            $field = DoktorandenFields::find('promotionsfach');
            foreach($faecher as $fach){
                $faecher_array[] = $field->getValueLIDByUniquename((int)$fach['fach_id']);
            }
            if (sizeof($faecher_array) >0){
                return $faecher_array;
            } else return false;
        } else return false;
    }
    
    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        $earliest_birthday = DateTime::createFromFormat($format, '1910-01-01');
        $latest_birthday = new DateTime(date($format)); //today
        if(!$d || ($d->format($format) != $date) || ($d < $earliest_birthday) || ($d > $latest_birthday)){
            return false;
        } else return true;
    }
    
    
    // customized #url_for for plugins
    public function url_for($to = '')
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }
    
    
    
}
