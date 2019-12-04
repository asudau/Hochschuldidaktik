
<?= CSRFProtection::tokenTag() ?>
<table class="sortable-table default">
 
    <caption>
       <?= _('Teilnehmende') ?> ( <?= sizeof($members_courses) ?> )

    </caption>
    <thead>
    <tr>
        <th data-sort="htmldata" style='width:15%'>
           <?= _('User') ?>
        </th>
        <th data-sort="htmldata" style='width:15%'>
           <?= _('Kostenstelle') ?>
        </th>
        <th data-sort="htmldata" style='width:5%'>
           <?= _('Anzahl Workshops') ?>
        </th>
        </th>
       <th data-sort="false" style='width:60%'>
           <?= _('Seminare') ?>
        </th>
        <th data-sort="htmldata" style='width:5%'>
           <?= _('Letzte Aktivität') ?>
        </th>
       
      
    </tr>
   
    </thead>
    
    <tbody>
    <? foreach ($members_courses as $user_id => $infos) : ?>
    <tr>
        <? $user = User::find($user_id); ?>
        <td data-sort-value=<?= $user->Nachname ?>><?= $user->Nachname ?>, <?= $user->Vorname ?></td>
        <td>
        <? $entries = DataFieldEntry::getDataFieldEntries($user_id); ?>
         <a  data-dialog="size=auto" href="<?= $controller->url_for("index/edit_kostenstelle/" . $user_id )?>">
            <?= Icon::create('edit', 'clickable') ?>
         </a>
        <? if ($entries[$datafield_id_kostenstelle]->value): ?>
            <?= substr($entries[$datafield_id_kostenstelle]->value, 0, 9) ?>
            <? if (strlen($entries[$datafield_id_kostenstelle]->value) > 9): ?>
            <?= Icon::create('info-circle', 'clickable', array('title'=>$entries[$datafield_id_kostenstelle]->value)) ?>
            <? endif ?> 
        <? endif ?> 
       
        </td>
        <td data-sort-value=<?= sizeof($infos['courses']) ?>><?= sizeof($infos['courses']) ?></td>
        <td>
            <? foreach ($infos['courses'] as $sem_info) : ?>
            <div style='margin:3px;'>
                <a title='Zur Veranstaltung' href='<?=URLHelper::getLink("/seminar_main.php?auswahl=" . $sem_info['id'] )?>'>
                    <?= explode('(', $sem_info['name'])[0] ?> (<?= Semester::findOneByBeginn($sem_info['beginn'])->name ?>)
                </a>
                <?= (ZertifikatsprogrammExamConfirmed::find([$sem_info['id'], $user_id ])) ? Icon::create('accept', Icon::ROLE_ACCEPT, array('title'=>'Teilnahme bestätigt')) : '' ?>
            </div> 
             <? endforeach ?>
             <? foreach (ZertifikatsprogrammExamConfirmed::findByUser_id($user_id) as $module) : ?>
                <? if ($module->modul) : ?>
                    <div style='margin:3px;'>
                        <?= $module->modul ?>
                        <?= Icon::create('accept', Icon::ROLE_ACCEPT, array('title'=>'Bestätigt')) ?>
                    </div>
                <? endif ?>
             <? endforeach ?>
             <div> 
                 <a  data-dialog="size=auto" href="<?= $controller->url_for("index/add_exam/" . $user_id )?>">
                <?= Icon::create('add', 'clickable', array('title'=>'(Externe) Bestätigte Leistung nachtragen')) ?>
                </a>
             </div>
        </td>
        <td data-sort-value=<?= $infos['latest_course'] ?>>
           <?= Semester::findOneByBeginn($infos['latest_course'])->name ?>
        </td>
    </tr>
        <? endforeach ?>
    </tbody>

</table>



