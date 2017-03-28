<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 25.02.2017
 * Time: 21:42
 */

namespace app\common\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class GetRoleBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'getRole'
        ];
    }

    public function getRole()
    {
        $authManager = \Yii::$app->authManager;

        if ($authManager === null)
            throw new \Exception('GetRoleBehavior::getRole AuthManager not found');

        $user = $this->owner;

        $assignments = $authManager->getAssignments($user->id);

        if (isset($assignments['admin']))
            $user->role = 'admin';
        else if (isset($assignments['manager']))
            $user->role = 'manager';
        else if (isset($assignments['deliveryman']))
            $user->role = 'deliveryman';
        else
            $user->role = null;
    }
}