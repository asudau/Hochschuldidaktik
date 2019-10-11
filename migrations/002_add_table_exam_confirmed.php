<?php

/**
 * @author Annelene Sudau <asudau@uos.de>
 */
class AddTableExamConfirmed extends Migration
{

    public function description()
    {
        return 'add table zertifikatsworkshop teilnahme confirmed';
    }

    public function up()
    {
        $db = DBManager::get();
        
        $stm = $db->prepare(
            "CREATE TABLE IF NOT EXISTS `zertifikatsprogramm_exam_confirmed` (
            `course_id` varchar(32) NOT NULL ,
            `user_id` varchar(128) NOT NULL,
            `modul` varchar(128) NOT NULL,
            PRIMARY KEY (course_id, user_id)
        )"
        );

        $stm->execute();

           
    }

    public function down()
    {
        DBManager::get()->exec(

            "DROP TABLE zertifikatsprogramm_exam_confirmed"
        );
        
    }
}
