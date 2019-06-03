<?php

/**
 * @author Annelene Sudau <asudau@uos.de>
 */
class AddUserDatafields extends Migration
{

    public function description()
    {
        return 'add user datafields for hochschuldidaktik kostenstelle and zertifikatsinfo';
    }

    public function up()
    {
        $db = DBManager::get();
        
        $stm = $db->prepare(
            "INSERT INTO `datafields` (`datafield_id`, `name`, `object_type`,
                `object_class`, `edit_perms`, `view_perms`, `priority`,
                `mkdate`, `chdate`, `type`, `typeparam`, `is_required`, `description`)
            VALUES (md5('hd_kostenstelle'), 'Hochschuldidaktik Kostenstelle', 'user',
                NULL, 3, 'root', '0', NULL, NULL, 'textline', '', '0', 'Hochschuldidaktik Kostenstelle für ILV')"
        );

        $stm->execute();

        
        $stm = $db->prepare(
            "INSERT INTO `datafields` (`datafield_id`, `name`, `object_type`,
                `object_class`, `edit_perms`, `view_perms`, `priority`,
                `mkdate`, `chdate`, `type`, `typeparam`, `is_required`, `description`)
            VALUES (md5('zertifikaterwerb'), 'Zertifikaterwerb', 'user',
                NULL, 3, 'root', '0', NULL, NULL, 'date', '', '0', 'Zertifikat abgeschlossen am:')"
        );
      
        $stm->execute();
           
    }

    public function down()
    {
        DBManager::get()->exec(

            "DELETE FROM datafields WHERE datafield_id "
                . "IN(md5('hd_kostenstelle'))"
        );
         DBManager::get()->exec(

            "DELETE FROM datafields WHERE datafield_id "
                . "IN(md5('zertifikaterwerb'))"
        );
    }
}
