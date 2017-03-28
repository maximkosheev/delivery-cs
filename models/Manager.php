<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 06.12.2016
 * Time: 19:59
 */

namespace app\models;

use yii\base\Security;
use yii\helpers\Url;

/**
 * Class Manager
 * @package app\models
 */
class Manager extends Worker
{
    public function behaviors()
    {
        return [
            'assignRole' => [
                'class' => 'app\common\behaviors\AssignRoleBehavior',
                'role' => 'manager'
            ],
            'upload' => [
                'class' => 'app\common\behaviors\UploadFileBehavior',
                'destDir' => \Yii::getAlias('@uploads/photos/'),
                'destName' => function() {
                    return (new Security())->generateRandomString(8);
                },
                'destAttr' => 'photo'
            ]
        ];
    }

    public static function getIndexUrl()
    {
        return Url::to(['user/managers']);
    }
}