<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 9/26/17
 * Time: 2:01 PM
 */

namespace console\components;


use yii\mongodb\console\controllers\MigrateController;

class FixedMigrateController extends MigrateController
{
    protected function createMigration($class)
    {
        $file = $this->migrationPath[0] . DIRECTORY_SEPARATOR . $class . '.php';
        require_once($file);

        return new $class(['db' => $this->db]);
    }
}