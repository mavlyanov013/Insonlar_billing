<?php

class m170926_085313_add_admin_user extends \yii\mongodb\Migration
{
    public function up()
    {
        $admin = new \common\models\Admin([
                                              'scenario'     => 'insert',
                                              'login'        => 'admin',
                                              'telephone'    => '+998909979114',
                                              'email'        => 'admin@activemedia.uz',
                                              'confirmation' => 'random1',
                                              'password'     => 'random1',
                                              'fullname'     => 'Shavkat',
                                              'status'       => \common\models\Admin::STATUS_ENABLE,
                                          ]
        );

        $admin->save();
    }

    public function down()
    {
        \common\models\Admin::deleteAll(['login' => 'admin']);
    }
}
