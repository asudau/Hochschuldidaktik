<html>

<body>
    <div>
<h1></h1>

<table id='doktoranden-entries' class="tablesorter default">
    <thead>
		<tr>
            <th style='width:10%'>Aktionen</th>
            <?php foreach($fields as $field): ?>
                <th><span><?= $field['name'] ?></span></th>   
            <?php endforeach ?>
            <?php foreach($additionalfields as $field => $contents): ?>
                <th><span><?= $field ?></span></th>   
            <?php endforeach ?>
        <!--<th>Courseware besucht?</th>-->
        </tr>
    </thead>
    <tbody>
    <?php foreach ($entries as $entry): ?>
    <tr>
        <td><a href='<?=$this->controller->url_for('index/edit/' . $entry['id']) ?>' title='Eintrag editieren' data-dialog="size=auto;reload-on-close"><?=Icon::create('edit')?></a><br/></td>
        <?php foreach($fields as $field): ?>
            <? $name = $field['name']; ?>
            <td><?= $entry->$name ?></td>
        <?php endforeach ?>
         <?php foreach($additionalfields as $field => $contents): ?>
            <td><?= $entry->$field ?></td>   
        <?php endforeach ?>

    </tr>
    <?php endforeach ?>
    </tbody>
</table>
</div>


</body>


<script type="text/javascript">

    
$(function(){
  $('#doktoranden-entries').tablesorter(); 
});


</script>



