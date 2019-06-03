<? use Studip\Button, Studip\LinkButton; ?>

<table class="sortable-table default">
 
    <caption>
       <?= _('Teilnehmende') ?>

    </caption>
    <thead>
    <tr>
        <th data-sort="htmldata" style='width:25%'>
           <?= _('Name') ?>
        </th>
        <th data-sort="htmldata" style='width:25%'>
           <?= _('Fachbereiche') ?>
        </th>

        <th data-sort="text" style='width:25%'>
           <?= _('Kostenstelle/Rechnungsdaten') ?>
        </th>
       
      
    </tr>
   
    </thead>
    
    <tbody>
        
<? foreach ($course_members as $member) : ?>
    <tr>
        <td><?= $member->Vorname . ' ' . $member->Nachname ?></td>
        <? $user = User::find($member->user_id); ?>
        <td>
            <? foreach($user->institute_memberships as $membership): ?>
                <?= $membership->institute->name?><br>
            <? endforeach ?>
        </td>
        <td>
        <? $datafield_id_erwerb = md5('zertifikaterwerb'); ?>
        
        <? $entries = DataFieldEntry::getDataFieldEntries($member->user_id); ?>
        <? if ($entries[$datafield_id_kostenstelle]->value): ?>
            <?= substr($entries[$datafield_id_kostenstelle]->value, 0, 9) ?>
            <? if (strlen($entries[$datafield_id_kostenstelle]->value) > 9): ?>
            <?= Icon::create('info', 'clickable', array('title'=>$entries[$datafield_id_kostenstelle]->value, 'onClick' => 'alert("'. $entries[$datafield_id_kostenstelle]->value . '")')) ?>
            <? endif ?> 
        <? endif ?> 
        
        </td>
    </tr>
<? endforeach ?>

    </tbody>
</table>



