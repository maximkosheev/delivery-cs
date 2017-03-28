<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 07.12.2016
 * Time: 14:08
 */
namespace app\common\rbac;

use app\models\Worker;
use yii\rbac\Item;
use yii\rbac\Rule;

class DeliverymanRule extends Rule
{
    public $name = 'isDeliveryman';
    /**
     * Executes the rule.
     *
     * @param string|integer $user the user ID. This should be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to [[ManagerInterface::checkAccess()]].
     * @return boolean a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if (!isset($params['user_id']))
            return false;

        $authManager = \Yii::$app->authManager;
        return $authManager->getAssignment('deliveryman', $params['user_id']) !== null ? true : false;
    }
}