<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 02.04.2017
 * Time: 13:15
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = 'Финансы';

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $this->title],
    ]
]);

echo Html::a('Отправить денег', ['finances/receipt'], ['class' => 'btn btn-lg btn-success']);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'emptyText' => 'Ничего не найдено',
    'summary' => 'Показано <b>{begin, number}-{end, number}</b> из <b>{totalCount, number}</b> {totalCount, plural, one{курьер} other{курьеров}}.',
    'columns' => [
        [
            'attribute' => 'fio',
            'value' => function(\app\models\Deliveryman $deliveryman) {
                return Html::a($deliveryman->fio, ['finances/deliveryman', 'id' => $deliveryman->user_id]);
            },
            'format' => 'raw'
        ],
        [
            'attribute' => 'balance',
            'value' => function(\app\models\Deliveryman $deliveryman) {
                return $deliveryman->balance;
            },
            'format' => 'text'
        ]
    ],
]);