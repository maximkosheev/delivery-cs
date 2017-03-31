<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 24.02.2017
 * Time: 0:19
 */

use yii\helpers\Url;
use app\models\Package;

if (Yii::$app->user->identity === null)
    return [];


if (Yii::$app->user->identity->isAdmin)
    return [
        ['label'=>'Курьеры', 'url'=>Url::to(['user/deliverymans'])],
        ['label'=>'Менеджеры', 'url'=>Url::to(['user/managers'])],
        ['label'=>'Новая заявка', 'url'=>Url::to(['package/create'])],
        ['label'=>'Все заявки', 'url'=>Url::to(['package/index'])],
        ['label'=>'Заявки по статусам', 'items' => [
            ['label'=>'Вакантные', 'url'=>Url::to(['package/index', 'status' => Package::STATUS_NOT_APPLIED])],
            ['label'=>'Открытые', 'url'=>Url::to(['package/index', 'status' => Package::STATUS_OPEN])],
            ['label'=>'Доставленные', 'url'=>Url::to(['package/index', 'status' => Package::STATUS_DELIVERED])],
            ['label'=>'Возвраты', 'url'=>Url::to(['package/index', 'status' => Package::STATUS_BACKOFF])],
        ]],
        ['label'=>'Отчеты', 'items' => [
            ['label'=>'История заявок', 'url'=>Url::to(['package/history'])],
        ]],
        ['label'=>'Финансы', 'url'=>Url::to(['finance/receipt'])]
    ];
else if (Yii::$app->user->identity->isManager)
    return [
        ['label' => 'Курьеры', 'url' => Url::to(['user/deliverymans'])],
        ['label'=>'Новая заявка', 'url'=>Url::to(['package/create'])],
        ['label'=>'Все заявки', 'url'=>Url::to(['package/index'])],
        ['label'=>'Заявки по статусам', 'items' => [
            ['label'=>'Вакантные', 'url'=>Url::to(['package/index', 'status' => Package::STATUS_NOT_APPLIED])],
            ['label'=>'Открытые', 'url'=>Url::to(['package/index', 'status' => Package::STATUS_OPEN])],
            ['label'=>'Доставленные', 'url'=>Url::to(['package/index', 'status' => Package::STATUS_DELIVERED])],
            ['label'=>'Возвраты', 'url'=>Url::to(['package/index', 'status' => Package::STATUS_BACKOFF])],
        ]],
        ['label'=>'Отчеты', 'items' => [
            ['label'=>'История заявок', 'url'=>Url::to(['package/history'])],
        ]],
    ];
else
    return [
        ['label'=>'Вакантные', 'url'=>Url::to(['package/index', 'status' => 0])],
        ['label'=>'Текущие', 'url'=>Url::to(['package/index', 'status' => 'current'])],
        ['label'=>'История', 'url'=>Url::to(['package/history2'])],
    ];