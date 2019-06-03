<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->url_for('index/edit_kostenstelle/' . $user_id . '/' . $course_id ) ?>" class="studip_form" method="POST">
    <fieldset>

        <label for="student_search" class="caption">
            <?= _('Kostenstelle eintragen')?>
        </label>

        <input type='text' name="kostenstelle" value="<?= ($kostenstelle) ? $kostenstelle->value : ''?>" ></input><br>
    </fieldset>

      <?= Button::createAccept(_('Speichern'), 'submit') ?>

</form>