<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 01.12.2016
 * Time: 22:48
 */

namespace app\modules\api;

use yii\base\Module;

class ApiModule extends Module
{
    public function init()
    {
        parent::init();
        \Yii::configure($this, require(__DIR__ .'/config.php'));
    }
}