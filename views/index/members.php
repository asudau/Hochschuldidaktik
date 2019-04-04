
<?= CSRFProtection::tokenTag() ?>
<table class="sortable-table default">
 
    <caption>
       <?= _('Teilnehmende') ?> ( <?= sizeof($members_courses) ?> )

    </caption>
    <thead>
    <tr>
        <th data-sort="htmldata" style='width:5%'>
           <?= _('User') ?>
        </th>
        
        </th>
       <th data-sort="false" style='width:20%'>
           <?= _('Seminare') ?>
        </th>
       
      
    </tr>
   
    </thead>
    
    <tbody>
    <? foreach ($members_courses as $user_id => $sem_ids) : ?>
    <tr>
        <? $user = User::find($user_id); ?>
        <td data-sort-value=<?= $course->start_semester->beginn ?>><?= $user->Nachname ?>, <?= $user->Vorname ?></td>
        
        <td>
            <? foreach ($sem_ids as $sem_id) : ?>
            <div style='background-color:#ddd; margin:3px;'>
                <? $sem = Course::find($sem_id); ?>
                <?= $sem->name ?> (<?= $sem->start_semester->name ?>) 
            </div>
             <? endforeach ?>
        </td>
    </tr>
        <? endforeach ?>
    </tbody>

</table>



