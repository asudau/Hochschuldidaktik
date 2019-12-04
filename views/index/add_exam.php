<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->url_for('index/add_exam/' . $user_id ) ?>" class="studip_form" method="POST">
    <fieldset>

        <label for="student_search" class="caption">
            <?= _('Modulbezeichnung und ggf. Erläuterung')?>
        </label>

        <input type='text' name="modul_erlaeuterung" value="<?= ($module) ? $module->value : ''?>" ></input><br>
    </fieldset>

      <?= Button::createAccept(_('Speichern'), 'submit') ?>

</form>