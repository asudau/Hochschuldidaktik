

<?= CSRFProtection::tokenTag() ?>
<table class="sortable-table default">
 
    <caption>
       <?= sizeof($workshops) . _(' Veranstaltung(en)') ?>

    </caption>
    <thead>
    <tr>
        <th data-sort="htmldata" style='width:5%'>
           <?= _('Semester') ?>
        </th>
        <th data-sort="htmldata" style='width:35%'>
           <?= _('Termin') ?>
        </th>

        <th data-sort="text" style='width:5%'>
           <?= _('Modul') ?>
        </th>
        
        <th data-sort="text" style='width:25%'>
           <?= _('Name') ?>
        </th>
      
        <th data-sort="false">
           <?= _('Lehrende') ?>
        </th>
       
        <th data-sort="false">
           <?= _('Teilnehmende/Max') ?>
        </th>
        <th data-sort="false">
            <?= _('Anmeldeset') ?>
        </th>
        <th data-sort="text" style='width:5%'>
           <?= _('Sichtbar') ?>
        </th>
       <th data-sort="false" style='width:20%'>
           <?= _('Ankündigungen') ?>
        </th>
        <th data-sort="false" style='width:5%'>
           <?= _('Aktionen') ?>
        </th>
       
      
    </tr>
   
    </thead>
    
    <tbody>
    <? foreach ($workshops as $course) : ?>
    <tr>
        <td data-sort-value=<?= $course->start_semester->beginn ?>><?= $course->start_semester->name ?></td>
        <td data-sort-value=<?= $course->dates[0] ? $course->dates[0]->date : null ?> >
            <?php if ($course->dates[0]) : ?>
               <?=  date('d.m.Y, H:i', $course->dates[0]->date) . '-' . date('H:i', $course->dates[0]->end_time) . ' Uhr' ?>
                    <?=  $course->dates[0]->raum  ? '(' . $course->dates[0]->raum . ') <br/>' : '' ?>
                    <?=  $course->dates[0]->room_booking ? '(' . $course->dates[0]->room_booking->resource->name . ') <br/>' : '' ?>
                   <?php if ($course->dates[1]) : ?>
                    <br/>
                    <?=  date('d.m.Y, H:i', $course->dates[1]->date) . '-' . date('H:i', $course->dates[1]->end_time) . ' Uhr' ?>
                    <?=  $course->dates[1]->raum  ? '(' . $course->dates[1]->raum . ') <br/>'  : '' ?>
                    <?=  $course->dates[1]->room_booking ? '(' . $course->dates[1]->room_booking->resource->getName() . ') <br/>' : '' ?>

                    <?php if ($course->dates[2]) : ?>
                    <br/>
                    <?=  date('d.m.Y, H:i', $course->dates[2]->date) . '-' . date('H:i', $course->dates[2]->end_time) . ' Uhr' ?>
                    <?=  $course->dates[2]->raum  ? '(' . $course->dates[2]->raum . ') <br/>'  : '' ?>
                    <?=  $course->dates[2]->room_booking ? '(' . $course->dates[2]->room_booking->resource->getName() . ') <br/>' : '' ?>
                    <? endif ?>
                <? endif ?>
                    <?=  $course->dates[1]->room_booking ? '(' . $course->dates[1]->room_booking->resource->name . ') <br/>' : '' ?>
             <? endif ?>
            <? else : ?>
            <?= 'Keine Termine'?>
        <? endif ?>
        </td>
        <td><?= substr($course->name, 0, 3)?></td>
        <td><a target='_blank' href="<?= URLHelper::getLink("seminar_main.php?cid=" . $course->id)?>"><?= $course->name?></a></td>
        <td><?= $course->countMembersWithStatus('dozent')?></td>
        <td><a target='_blank' href="<?= URLHelper::getLink("dispatch.php/course/members?cid=" . $course->id)?>">
                           <?= $course->countMembersWithStatus('autor')?>
                           <?= $course->admission_turnout ? '/' . $course->admission_turnout : '' ?> 
            </a>
        </td>
        <td>
            <a target="_blank" href="<?=URLHelper::getLink("dispatch.php/course/admission/", ['cid' => $course->id]) ?>">
            <? $seminar = new Seminar($course->id); ?>
            <? $courseset = $seminar->getCourseSet(); ?>
            <? if ($courseset && $courseset->hasAdmissionRule('TimedAdmission')): ?>
            <?= Icon::create('accept', Icon::ROLE_STATUS_GREEN, ['title' => 'Anmeldung möglich bis ' . date("d.m.Y", $courseset->getAdmissionRule('TimedAdmission')->getEndTime())]) ?>
            <? else : ?>
            <?= Icon::create('decline', Icon::ROLE_STATUS_RED, ['title' => 'Anmeldezeitraum nicht konfiguriert']) ?>
            <? endif ?>
            /
            <? if ($courseset && $courseset->hasAdmissionRule('ParticipantRestrictedAdmission')) : ?>
                <? if ($courseset->getAdmissionRule('ParticipantRestrictedAdmission')->getDistributionTime() == 0) : ?>
                           <?= Icon::create('accept', Icon::ROLE_STATUS_GREEN, ['title' => 'TN-Zahl begrenzt auf ' . $course->admission_turnout]) ?>
                <? else : ?>
                    <?= Icon::create('decline', Icon::ROLE_STATUS_RED, ['title' => 'Es wurde ein Zeitpunkt für die Platzverteilugn konfiguriert. Das soll nicht so sein.']) ?>
                <? endif ?>
            <? else :?>
                <?= Icon::create('decline', Icon::ROLE_STATUS_RED, ['title' => 'TN-Zahl nicht begrenzt']) ?>
            <? endif ?>
            </a>
        
        <td><?= $course->visible ? 'Ja' : 'Nein' ?></td>
        <td>
            <?php if(StudipNews::GetNewsByRange($course->id, true, true)) : ?>
                <? foreach(StudipNews::GetNewsByRange($course->id, true, true) as $news) : ?>
                <div style='background-color:#ddd; margin:3px;'>
                <a href="<?=URLHelper::getLink("dispatch.php/news/edit_news/" . $news->id , ['cid' => $course->id]) ?>" rel="get_dialog">
                    <?= $news->topic ?>
                </a>
                </div>
                <? endforeach ?>
            <? endif ?>
            <div style='background-color:#ddd; margin:3px;'>
                <a href="<?=URLHelper::getLink("dispatch.php/news/edit_news/new/" . $course->id) ?>" rel="get_dialog">
                    <?= Icon::create('add', 'clickable')?>  Neue Ankündigung
                </a>
            </div></br>
        </td>
        <td>
            <a title='Abrechnung TN-Beiträge' data-dialog="size=medium" href="<?= $controller->url_for("index/participant_fees/" . $course->id)?>"><?= Icon::create('euro', 'clickable') ?></a>
            <a title='Teilnahmen bestätigen' data-dialog="size=medium" href="<?= $controller->url_for("index/participant_confirm/" . $course->id)?>">
                    <? if (ZertifikatsprogrammExamConfirmed::findOneByCourse_id($course->id)) : ?>
                        <?= Icon::create('accept',  Icon::ROLE_STATUS_GREEN, ['title' => 'Wurde eingetragen']) ?>
                    <? else: ?>
                        <?= Icon::create('accept', 'clickable') ?>
                    <? endif ?>
            </a>
            <a title='Workshop kopieren' data-dialog="size=medium" href="<?=URLHelper::getLink("dispatch.php/course/wizard/copy/" . $course->id) ?>"><?= Icon::create('seminar+add', 'clickable') ?></a>
            </td>
    </tr>
        <? endforeach ?>
    </tbody>

</table>



