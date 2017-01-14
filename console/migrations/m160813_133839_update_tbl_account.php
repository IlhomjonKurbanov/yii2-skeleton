<?php

use yii\db\Migration;

/**
 * Class m160813_133839_update_tbl_account
 */
class m160813_133839_update_tbl_account extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%core_account}}', 'access_token', $this->string()->after('auth_key'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%core_account}}', 'access_token');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
