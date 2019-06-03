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
        $this->allworkshops = Course::findBySQL("Name LIKE '%Hochschuldidaktische Qualifizierung%' ");
        $this->datafield_id_kostenstelle = md5('hd_kostenstelle');
    }

    public function index_action()
    {
        Navigation::activateItem('tools/hochschuldidaktik/index');
        
        
        $study_area_zertifikate = StudipStudyArea::find(Hochschuldidaktik::STUDY_AREA_ID );
        //$workshops = $study_area_zertifikate->courses;
        
        foreach($this->allworkshops as $course){
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
    
     public function members_action()
    {
        Navigation::activateItem('tools/hochschuldidaktik/members');
        
        $this->search = isset($_GET['search_user'])? studip_utf8encode($_GET['search_user']) : NULL;
        
        $search_user = new SearchWidget($this->url_for('index/members'));
        $search_user->addNeedle(_('Nutzer'), 'search_user', true, null, null, studip_utf8decode($this->search));
        Sidebar::get()->addWidget($search_user);
        
        $actions = new ActionsWidget();               
        $actions->addLink(_('Nutzer suchen'),
                    $this->url_for('index/'),
                    //$GLOBALS['ABSOLUTE_URI_STUDIP'] . "dispatch.php/calendar/single/edit/" . $this->sem_id,
                          Icon::create('add', 'clickable'), ["rel" => "get_dialog", "dialog-title" => 'Nutzer suchen']);
        
        $this->members_courses = self::getMembers();
        
        
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
    
  
    
    
    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        $earliest_birthday = DateTime::createFromFormat($format, '1910-01-01');
        $latest_birthday = new DateTime(date($format)); //today
        if(!$d || ($d->format($format) != $date) || ($d < $earliest_birthday) || ($d > $latest_birthday)){
            return false;
        } else return true;
    }
    
    private function getMembers(){
        
        if (!$this->search){
            $this->search = '%%%';
        }
         $stmt = DBManager::get()->prepare("SELECT seminar_user.user_id, seminar_user.Seminar_id, auth_user_md5.Vorname, auth_user_md5.Nachname from seminare "
                 . "LEFT JOIN seminar_user USING(Seminar_id)"
                 . "LEFT JOIN auth_user_md5 USING(user_id)"
                 . "WHERE seminare.Name LIKE '%Hochschuldidaktische Qualifizierung%' "
                 . "AND seminar_user.status = 'autor' "
                 . "AND (auth_user_md5.Nachname LIKE :input "
                 . "OR auth_user_md5.Vorname LIKE :input "
                 . "OR auth_user_md5.username LIKE :input)"
                 . "ORDER BY auth_user_md5.Nachname");
        $stmt->execute([':input' => '%' . $this->search . '%']);
        $member_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //$i = 0;
        $members_courses = []; //new SimpleORMapCollection();
        //$event_collection->setClassName('Event');
        foreach ( $member_data as $row) {
            $members_courses[$row['user_id']][] = $row['Seminar_id'];
        }
        return $members_courses;
    }
    
    public function participant_fees_action($course_id){
        $this->course = Course::find($course_id);
        $this->course_members = $this->course->getMembersWithStatus('autor');
        
    }
    
    public function edit_kostenstelle_action($user_id){
        $this->user_id = $user_id;
        $localEntries = DataFieldEntry::getDataFieldEntries($user_id);
        $this->kostenstelle = $localEntries[$this->datafield_id_kostenstelle ];
        if (Request::get('kostenstelle')){
            $this->kostenstelle->setValue(Request::get('kostenstelle'));
            $this->kostenstelle->store();
            $this->redirect($this->url_for('index/members'));
        }
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
