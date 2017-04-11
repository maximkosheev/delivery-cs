<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 23.12.2016
 * Time: 12:20
 */

namespace app\components\widgets;

use yii\base\Widget;

class ActionStatusMessage extends Widget
{
    public $successKey;
    public $failedKey;

    public function init()
    {
        $this->successKey = 'actionSuccess';
        $this->failedKey = 'actionFailed';
        parent::init();
    }

    public function run()
    {
        $out = '';
        $successMessage = \Yii::$app->session->getFlash($this->successKey, null);
        if ($successMessage !== null)
            $out =  '<div class="alert alert-success">'.$successMessage.'</div>';

        $failedMessage = \Yii::$app->session->getFlash($this->failedKey, null);
        if ($failedMessage !== null)
            $out = '<div class="alert alert-danger">'.$failedMessage.'</div>';

        return $out;
    }
}