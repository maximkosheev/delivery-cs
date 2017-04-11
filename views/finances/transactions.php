<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 02.04.2017
 * Time: 2:15
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

use yii\widgets\Breadcrumbs;
use yii\grid\GridView;
use app\models\FinancesLog;

$this->title = 'Движение денежных стредств';

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $this->title],
    ]
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'emptyText' => 'Ничего не найдено',
    'summary' => 'Показано <b>{begin, number}-{end, number}</b> из <b>{totalCount, number}</b> {totalCount, plural, one{запись} other{записей}}.',
    'columns' => [
        [
            'label' => 'time',
            'headerOptions' => [
                'width' => '150px'
            ]
        ],
        [
            'label' => 'deliveryman_id',
            'value' => function(FinancesLog $data) {
                return $data->deliveryman->fio;
            }
        ],
        'cash',
        'description'
    ],
]);