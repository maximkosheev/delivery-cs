<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 11.12.2016
 * Time: 1:23
 */

namespace app\common\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class AssignRoleBehavior extends Behavior
{
    public $role;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'assignRole'
        ];
    }

    public function assignRole()
    {
        $authManager = \Yii::$app->authManager;
        $role = $authManager->getRole($this->role);

        if ($role === null)
            throw new ServerErrorHttpException('Ошибка конфигурации rbac сервера');

        $authManager->assign($role, $this->owner->identity->id);
    }
}