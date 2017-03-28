<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 07.12.2016
 * Time: 12:38
 */

namespace app\commands;

use app\common\rbac\DeliverymanRule;
use app\common\rbac\OwnRule;
use app\common\rbac\CanUpdateDeliveryRule;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $authManager = \Yii::$app->authManager;

        $ownRule = new OwnRule();
        $deliverymanRule = new DeliverymanRule();
        $canUpdateDeliveryRule = new CanUpdateDeliveryRule();

        $authManager->removeAll();

        $authManager->add($ownRule);
        $authManager->add($deliverymanRule);
        $authManager->add($canUpdateDeliveryRule);

        $admin = $authManager->createRole('admin');
        $authManager->add($admin);
        $manager = $authManager->createRole('manager');
        $authManager->add($manager);
        $authManager->addChild($admin, $manager);
        $deliveryman = $authManager->createRole('deliveryman');
        $authManager->add($deliveryman);
        $authManager->addChild($manager, $deliveryman);

        $createManager = $authManager->createPermission('createManager');
        $createManager->description = 'Создание менеджеров';
        $authManager->add($createManager);
        $authManager->addChild($admin, $createManager);

        $removeProfile = $authManager->createPermission('removeProfile');
        $removeProfile->description = 'Удаление профиля пользователя';
        $authManager->add($removeProfile);
        $authManager->addChild($admin, $removeProfile);

        $createDeliveryman = $authManager->createPermission('createDeliveryman');
        $createDeliveryman->description = 'Создание курьеров';
        $authManager->add($createDeliveryman);
        $authManager->addChild($manager, $createDeliveryman);

        $removeDeliveryman = $authManager->createPermission('removeDeliveryman');
        $removeDeliveryman->description = 'Удаление курьеров';
        $removeDeliveryman->ruleName = $deliverymanRule->name;
        $authManager->add($removeDeliveryman);
        $authManager->addChild($manager, $removeDeliveryman);
        $authManager->addChild($removeDeliveryman, $removeProfile);

        $changePassword = $authManager->createPermission('changePassword');
        $changePassword->description = 'Изменение пароля пользователей';
        $authManager->add($changePassword);
        $authManager->addChild($admin, $changePassword);

        $createDelivery = $authManager->createPermission('createDelivery');
        $createDelivery->description = 'Создание заявок';
        $authManager->add($createDelivery);
        $authManager->addChild($manager, $createDelivery);

        $updateDelivery = $authManager->createPermission('updateDelivery');
        $updateDelivery->description = 'Редактирование данных заявки';
        $updateDelivery->ruleName = $canUpdateDeliveryRule->name;
        $authManager->add($updateDelivery);
        $authManager->addChild($manager, $updateDelivery);

        $removeDelivery = $authManager->createPermission('deleteDelivery');
        $removeDelivery->description = 'Удаление заявки';
        $authManager->add($removeDelivery);
        $authManager->addChild($manager, $removeDelivery);

        $viewDelivery = $authManager->createPermission('viewDelivery');
        $viewDelivery->description = 'Просмотр данных заявки';
        $authManager->add($viewDelivery);
        $authManager->addChild($deliveryman, $viewDelivery);

        $updateOwnProfile = $authManager->createPermission('updateOwnProfile');
        $updateOwnProfile->description = 'Обновление данных своего профиля';
        $updateOwnProfile->ruleName = $ownRule->name;
        $authManager->add($updateOwnProfile);
        $authManager->addChild($manager, $updateOwnProfile);

        $updateProfile = $authManager->createPermission('updateProfile');
        $updateProfile->description = 'Обновление профиль пользователя';
        $authManager->add($updateProfile);
        $authManager->addChild($admin, $updateProfile);
        $authManager->addChild($updateOwnProfile, $updateProfile);

        $updateDeliveryman = $authManager->createPermission('updateDeliveryman');
        $updateDeliveryman->description = 'Обновление профиля курьера';
        $updateDeliveryman->ruleName = $deliverymanRule->name;
        $authManager->add($updateDeliveryman);
        $authManager->addChild($updateDeliveryman, $updateProfile);
        $authManager->addChild($manager, $updateDeliveryman);
    }
}