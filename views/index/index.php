

<?= CSRFProtection::tokenTag() ?>
<table class="sortable-table default">
 
    <caption>
       <?= _('Veranstaltungen') ?>

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
        <th data-sort="text" style='width:5%'>
           <?= _('Sichtbar') ?>
        </th>
       <th data-sort="false" style='width:20%'>
           <?= _('Ankündigungen') ?>
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
                    <?=  $course->dates[0]->room_assignment ? '(' . $course->dates[0]->room_assignment->resource->getName() . ') <br/>' : '' ?>
                   <?php if ($course->dates[1]) : ?>
                    <br/>
                    <?=  date('d.m.Y, H:i', $course->dates[1]->date) . '-' . date('H:i', $course->dates[1]->end_time) . ' Uhr' ?>
                    <?=  $course->dates[1]->raum  ? '(' . $course->dates[1]->raum . ') <br/>'  : '' ?>
                    <?=  $course->dates[1]->room_assignment ? '(' . $course->dates[1]->room_assignment->resource->getName() . ') <br/>' : '' ?>
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
    </tr>
        <? endforeach ?>
    </tbody>

</table>



