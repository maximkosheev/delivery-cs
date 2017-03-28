<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 16.12.2016
 * Time: 14:17
 * @var \yii\web\View $this
 * @var \app\models\Worker $employer
 * @var \app\models\DeliverymanStatistic $statistic
 */

use app\models\Manager;
use app\models\Deliveryman;
use yii\widgets\Breadcrumbs;

$this->title = $employer->fio;

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $employer instanceof Manager ? 'Менеджеры' : 'Курьеры', 'url' => $employer->indexUrl],
        ['label' => $this->title],
    ]
]);

if ($employer instanceof Manager)
    echo $this->render('_viewManager', [
        'employer' => $employer
    ]);
else
    echo $this->render('_viewDeliveryman', [
        'employer' => $employer
    ]);
