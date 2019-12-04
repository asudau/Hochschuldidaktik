<?php

require_once __DIR__ . '/../models/ZertifikatsprogrammExamConfirmed.class.php';

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
        $this->allworkshops = Course::findBySQL("Name LIKE '%Hochschuldidaktische Qualifizierung%' AND Name NOT LIKE '%Multiplikator%'");
        $this->datafield_id_kostenstelle = md5('hd_kostenstelle');
    }

    public function index_action($selection = NULL)
    {
        Navigation::activateItem('tools/hochschuldidaktik/index');
        $views = new ViewsWidget();
        $views->addLink(_('Workshops der letzten 12 Monate'),
                        $this->url_for('index'))
              ->setActive($action === 'index');
        $views->addLink(_('Alle Workshops'),
                        $this->url_for('index/index/all'))
              ->setActive($action == 'index');
         $views->addLink(_('Workshops ohne Termin'),
                        $this->url_for('index/index/no_dates'))
              ->setActive($action == 'index');
        $views->addLink(_('Organisatorische Veranstaltungen'),
                        $this->url_for('index/index/special'))
              ->setActive($action == 'index');
        Sidebar::get()->addWidget($views);
        
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
        $this->recent_workshops = [];
        $this->allworkshops_sorted = [];
        
        if ($selection != 'no_dates'){
            $one_year_ago = time() - (60*60*24*365);
            foreach($workshops_with_date as $date => $course){
                //array_push($this->allworkshops_sorted, $course);
                if ($selection == 'all' || $date > $one_year_ago){
                    array_push($this->recent_workshops, $course);
                }
            }
        }
        if ($selection == 'special'){
            $this->workshops = [Course::find('e7b2ee63e275bb4b8fc864974785b03b')];
        } else {
            $this->workshops = array_merge($this->recent_workshops, $this->workshops_without_date);
        }
    }
    
     public function members_action($selection = NULL)
    {
        Navigation::activateItem('tools/hochschuldidaktik/members');
         $views = new ViewsWidget();
        $views->addLink(_('In den letzen Jahren aktiv'),
                        $this->url_for('index/members'))
              ->setActive($action === 'members');
        $views->addLink(_('Alle Teilnehmer*innen'),
                        $this->url_for('index/members/all'))
              ->setActive($action === 'members');
        Sidebar::get()->addWidget($views);
        
        $this->search = isset($_GET['search_user'])? studip_utf8encode($_GET['search_user']) : NULL;
        
        $search_user = new SearchWidget($this->url_for('index/members'));
        $search_user->addNeedle(_('Nutzer'), 'search_user', true, null, null, studip_utf8decode($this->search));
        Sidebar::get()->addWidget($search_user);
        
        $actions = new ActionsWidget();               
        $actions->addLink(_('Nutzer suchen'),
                    $this->url_for('index/'),
                    //$GLOBALS['ABSOLUTE_URI_STUDIP'] . "dispatch.php/calendar/single/edit/" . $this->sem_id,
                          Icon::create('add', 'clickable'), ["rel" => "get_dialog", "dialog-title" => 'Nutzer suchen']);
        
        $this->members_courses = self::getMembers($selection);
        
        
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
    
    private function getMembers($selection){
        
        if (!$this->search){
            $this->search = '%%%';
        }
         $stmt = DBManager::get()->prepare("SELECT seminar_user.user_id, seminare.start_time, seminare.Name, seminar_user.Seminar_id, auth_user_md5.Vorname, auth_user_md5.Nachname from seminare "
                 . "LEFT JOIN seminar_user USING(Seminar_id)"
                 . "LEFT JOIN auth_user_md5 USING(user_id)"
                 . "WHERE seminare.Name LIKE '%Hochschuldidaktische Qualifizierung%' "
                 . "AND seminare.Name NOT LIKE '%Multiplikator%' "
                 //. "AND seminar_user.status = 'autor' "
                 . "AND (auth_user_md5.Nachname LIKE :input "
                 . "OR auth_user_md5.Vorname LIKE :input "
                 . "OR auth_user_md5.username LIKE :input)"
                 . "ORDER BY auth_user_md5.Nachname");
        $stmt->execute([':input' => '%' . $this->search . '%']);
        $member_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $members_courses = []; 
        
        foreach ( $member_data as $row) {
            if(!in_array($row['user_id'], ['089316d254a3c0e7e74ad4a2f189ca8a', '23f8d0a297dcc23f292fe7bb358ad280', '50e72c7107ee0fbb6a09e774a95b69a7'])){ //samed, anne, frank
                $members_courses[$row['user_id']]['courses'][] = [ 'id' => $row['Seminar_id'], 
                        'name' => $row['Name'],
                        'beginn' => $row['start_time']
                    ];
                $members_courses[$row['user_id']]['latest_course'] = ($row['start_time'] > $members_courses[$row['user_id']]['latest_course']) ? $row['start_time'] : $members_courses[$row['user_id']]['latest_course'];
                }
        }
        
        //falls nicht alle, nur die die in den letzten 5 Jahren in einem Workshop waren
        $active_members = [];
        if ($selection != 'all'){
            $five_years_ago = time() - (5*60*60*24*365);
            foreach($members_courses as $uid => $array) {
                if($array['latest_course'] > $five_years_ago){
                    $active_members[$uid] = $array;
                }
            }
            $members_courses = $active_members;
        }
        
        //nach aktuellestem Workshopsemester sortieren
        $sortArray = array();
        foreach($members_courses as $uid => $array) {
                $sortArray[$uid] = $array['latest_course'];
        }
        array_multisort($sortArray, SORT_DESC, SORT_NUMERIC, $members_courses); 
        
 
        return $members_courses;
    }
    
    public function participant_fees_action($course_id){
        $this->course = Course::find($course_id);
        $this->course_members = $this->course->getMembersWithStatus('autor');
        
    }
    
    public function add_exam_action($user_id){
        $this->user_id = $user_id;
        if (Request::get('modul_erlaeuterung')){
            $entry = new ZertifikatsprogrammExamConfirmed([time(), $user_id]);
            $entry->modul = Request::get('modul_erlaeuterung');
            $entry->store();
            $message = MessageBox::success(_('Modul gespeichert'));
            PageLayout::postMessage($message);
            $this->redirect('index/members');
        }
    }
    
    public function participant_confirm_action($course_id){
        $this->course = Course::find($course_id);
        $this->course_members = $this->course->getMembersWithStatus(['autor', 'dozent']);
        
    }
    
     public function participant_confirm_save_action(){
        
        $course_id = Request::get('course_id');
        $this->course = Course::find($course_id);
        $this->course_members = Request::optionArray('confirm_exam');
        foreach($this->course_members as $course_member){
            $exam_confirmed = ZertifikatsprogrammExamConfirmed::find([$course_id, $course_member]);
            if (!$exam_confirmed){
            $exam_confirmed = new ZertifikatsprogrammExamConfirmed([$course_id, $course_member]);
                $exam_confirmed->store();
            }  
        }
        $message = MessageBox::success(_('Bestätigte Teilnahme gespeichert'));
        PageLayout::postMessage($message);
        $this->redirect('index');
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
