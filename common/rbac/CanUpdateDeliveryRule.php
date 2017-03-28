<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 22.12.2016
 * Time: 12:26
 */

namespace app\common\rbac;

use app\models\Package;
use yii\rbac\Item;
use yii\rbac\Rule;

class CanUpdateDeliveryRule extends Rule
{
    public $name = 'canUpdateDelivery';


    /**
     * Executes the rule.
     *
     * @param string|integer $user the user ID. This should be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to [[CheckAccessInterface::checkAccess()]].
     * @return boolean a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute($user, $item, $params)
    {
        $authManager = \Yii::$app->authManager;

        $assignments = $authManager->getAssignments($user);

        $identity = \Yii::$app->user->identity;

        if ($identity === null)
            return false;

        // администратор может редактировать любую заявку (даже закрытую)
        if ($identity->isAdmin) return true;
        //  курьер не может редактировать ни одной заявки
        if ($identity->isDeliveryman) return false;
        // вообще какая-то лажа (такого быть не должно)
        if (!isset($params['package'])) return false;

        // осталось рассмотреть только менеджеров. Они могут редактировать только открытые заявки
        $package = $params['package'];
        if ($identity->isManager &&
            $package->status < Package::STATUS_DELIVERED) return true;
        return false;
    }
}