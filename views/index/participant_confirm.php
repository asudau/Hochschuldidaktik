<? use Studip\Button, Studip\LinkButton; ?>

<form class="default" method="post" action="<?=$controller->url_for('index/participant_confirm_save')?>">

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
           <?= _('Teinahmebescheinigung erworben') ?>
        </th>
      
    </tr>
   
    </thead>
    
    <tbody>
            <input type="hidden" name="course_id" value="<?= $course->id?>" >

            <? foreach ($course_members as $member) : ?>
                <tr>
                    <td><?= $member->Vorname . ' ' . $member->Nachname ?></td>
                    <td>
                        <input type="checkbox" name="confirm_exam[]" value="<?= $member->user_id ?>" multiple="yes" 
                               <?= ZertifikatsprogrammExamConfirmed::find([$course->id, $member->user_id])? 'checked' : ''?> >
                    </td>
                </tr>
            <? endforeach ?>
        
    
    </tbody>
</table>

<footer data-dialog-button>
    <?= Button::create(_('Übernehmen')) ?>
</footer>
</form>


