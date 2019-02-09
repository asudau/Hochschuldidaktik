
<?
use Studip\Button, Studip\LinkButton;
?>

<html>


<form name="course-settings" name="settings" method="post" onsubmit="return validateForm()" action="<?= $controller->url_for('index/save', $entry->id) ?>" <?= $dialog_attr ?> class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <input id="open_variable" type="hidden" name="open" value="<?= $flash['open'] ?>">
    
    <?php foreach ($groupedFields as $group): ?>
    <fieldset <?= isset($flash['open']) && $flash['open'] != 'bd_basicsettings' ? 'class="collapsed"' : ''?> data-open="bd_basicsettings">
        <legend><?= $group['title'] ?></legend>
        <table>
            <?php foreach ($group['entries'] as $field_entry): ?>
            <? ($entry[$field_entry->id])? $value = $field_entry->getValueTextByKey($entry[$field_entry->id]): $value = NULL; ?>
            
            <tr name ='<?=$field_entry->id?>'  >
                <td style="width:500px" <? if ($entry->req($field_entry->id)): ?> class='required' <? endif ?> >
                    <?=$field_entry->title?>: 
                    <? if ($field_entry->helptext): ?>
                        <span class="info" title="<?=$field_entry->helptext ?>"><?= Icon::create('info-circle', 'info'); ?></span>
                    <? endif ?>
                </td>
                <td style="width:700px">
                    <?php if(sizeof($field_entry->values) > 1104) : ?>
                    
                    <?= QuickSearch::get($field_entry->id, $field_entry->search_object)
                        ->setInputStyle("width: 240px")
                        //->fireJSFunctionOnSelect('doktoranden_select')
                        ->defaultValue( $entry[$field_entry->id], $value) 
                        ->withButton()
                        ->render();?>
                    
                    <?php elseif (sizeof($field_entry->values) > 1) : ?>
                    <select <? if ($entry->disabled($field_entry->id)): ?> disabled <? endif ?>
                        class='nested-select' name ='<?=$field_entry->id?>'>
                        <option value="NULL"> <?= $entry->getDefaultOption($field_entry->id) ?> </option>
                        <?php foreach ($field_entry->values as $entry_value): ?>
                        <? $key = $field_entry->value_key; ?>
                            <option <? if ($entry[$field_entry->id] == $entry_value->$key) :?> selected <? endif ?> value="<?= $entry_value->$key ?>"><?= $entry_value->defaulttext . (($entry_value->uniquename)? ' ' . $entry_value->uniquename: '') ?></option>
                        <?php endforeach ?>
                    </select>
                    <?php elseif ($field_entry->id == 'geburtstag') : ?>
                    <input type='date' id ='<?=$field_entry->id?>' name ='<?=$field_entry->id?>' value='<?= $entry['geburtstag_time'] ? date('Y-m-d', $entry['geburtstag_time']) : ''?>'>
                    <?php else : ?>
                    <input type='text' id ='<?=$field_entry->id?>' name ='<?=$field_entry->id?>' 
                           <? if ($entry->disabled($field_entry->id)): ?> disabled placeholder=''<? else: ?>
                           value ='<?= $entry[$field_entry->id]?>' <? endif ?>> 
                    <?php endif ?>
                </td>
            </tr>
            <?php endforeach ?>
        </table>

       
    </fieldset>
     <?php endforeach ?>

    
    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>


<script>
    
    function validateForm() {
        var e = document.getElementsByName("promotionsfach")[1];
        var value = e.options[e.selectedIndex].value;
            if (value == "NULL") {
            alert("Promotionsfach muss angegeben werden!");
            return false;
        }
    }
    
    var inputs, index;
    
    //Ersteinschreibung Auslandshochschulen, dann Staat Pflichtfeld
    document.getElementsByName("hochschule_erst")[1].onchange = function () {
        if (this.value == '2'){
            document.getElementsByName("staat_hochschule_erst")[1].removeAttribute("disabled");
            //document.getElementsByName("staat_hochschule_erst")[0].style.display = "";
        } else {
            //document.getElementsByName("staat_hochschule_erst")[1].value = "-- nicht erforderlich --"; 
            document.getElementsByName("staat_hochschule_erst")[1].setAttribute("disabled", true); 
            //$("tr[staat_hochschule_erst]").hide();//.removeClass( "required");
            //document.getElementsByName("staat_hochschule_erst")[0].style.display = "none";//.removeAttribute("class");
        }
    };
    
    document.getElementsByName("art_reg_prom")[1].onchange = function () {
        if (this.value == '3' || this.value == '2' ){
            document.getElementsByName("promotionsende_monat")[1].removeAttribute("disabled");
            document.getElementsByName("promotionsende_jahr")[1].removeAttribute("disabled");
            //document.getElementsByName("staat_hochschule_erst")[0].style.display = "";
        } else {
            //document.getElementsByName("promotionsende_monat")[1].value = "-- nicht erforderlich --"; 
            document.getElementsByName("promotionsende_monat")[1].setAttribute("disabled", true); 
            document.getElementsByName("promotionsende_jahr")[1].setAttribute("disabled", true);
            //$("tr[staat_hochschule_erst]").hide();//.removeClass( "required");
            //document.getElementsByName("staat_hochschule_erst")[0].style.display = "none";//.removeAttribute("class");
        }

    };
    
    //Abschluss(HZB) im Ausland: Staat Pflichtfeld
    //Abschluss(HZB) im Inland: Staat ausgegraut, Bundesland und Kreis Pflichtfeld
    //TODO, astatwerte aus tabelle kramen
    var hzb_art_ids = new Array("41", "52", "15", "56", "25", "58");
    document.getElementsByName("hzb_art")[1].onchange = function () {
        if (hzb_art_ids.indexOf(this.value) != -1) {
            document.getElementsByName("hzb_staat")[1].removeAttribute("disabled");
            document.getElementsByName("hzb_kreis")[1].setAttribute("disabled", true);
        } else {
            document.getElementsByName("hzb_staat")[1].setAttribute("disabled", true); 
            document.getElementsByName("hzb_kreis")[1].removeAttribute("disabled");
        }
    };
    
    //Abschlusshochschule Auslandshochschulen, dann Staat Pflichtfeld
    document.getElementsByName("hochschule_abschlusspruefung")[1].onchange = function () {
        if (this.value == '2'){
            document.getElementsByName("staat_abschlusspruefung")[1].removeAttribute("disabled");
        } else {
            document.getElementsByName("staat_abschlusspruefung")[1].setAttribute("disabled", true);
        } 

    };
    
    $(function() {
            $('.mydate-picker').datepicker( {
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'MM yy',
            onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
            });
        });
    

</script>



