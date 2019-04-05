
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
    <? foreach ($members_courses as $user_id => $sem_ids) : ?>
    <tr>
        <? $user = User::find($user_id); ?>
        <td data-sort-value=<?= $user->Nachname ?>><?= $user->Nachname ?>, <?= $user->Vorname ?></td>
        <td data-sort-value=<?= sizeof($sem_ids) ?>><?= sizeof($sem_ids) ?></td>
        <td>
            <? $max_sem = Course::find($sem_ids[0])->start_semester; ?>
            <? foreach ($sem_ids as $sem_id) : ?>
            <div style='margin:3px;'>
                <? $sem = Course::find($sem_id); ?>
                <? $max_sem = ($sem->start_semester->beginn > $max_sem->beginn) ? $sem->start_semester : $max_sem; ?>
                <a title='Zur Veranstaltung' href='<?=URLHelper::getLink("/seminar_main.php?auswahl=" . $sem_id )?>'>
                    <?= explode('(', $sem->name)[0] ?> (<?= $sem->start_semester->name ?>)
                </a>
            </div>
             <? endforeach ?>
        </td>
        <td data-sort-value=<?= $max_sem->beginn ?>>
           <?= $max_sem->name ?>
        </td>
    </tr>
        <? endforeach ?>
    </tbody>

</table>



