<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 08.12.2016
 * Time: 11:03
 *
 * @var \yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;

$this->title = 'Список курьеров системы';

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $this->title],
    ]
]);

$actionSuccessMessage = Yii::$app->session->getFlash('actionSuccess', null);

if ($actionSuccessMessage !== null) {
    echo '<div class="alert alert-success">'.$actionSuccessMessage.'</div>';
}

echo Html::a('Добавить', Url::to(['user/create', 'type'=>'deliveryman']), ['class' => 'btn btn-lg btn-success']);

echo GridView::widget([
    'dataProvider' => $deliverymans,
    'emptyText' => 'Курьеров пока нет',
    'summary' => 'Показано <b>{begin, number}-{end, number}</b> из <b>{totalCount, number}</b> {totalCount, plural, one{курьер} other{курьеров}}.',
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'label' => 'Логин',
            'value' => function(\app\models\Deliveryman $data) {
                return Html::a($data->identity->username, Url::to(['user/view', 'id'=>$data->identity->id]));
            },
            'format' => 'raw',
            'headerOptions' => [
                'width' => '150px'
            ]
        ],
        'fio',
        [
            'class' => 'yii\grid\ActionColumn',
            'template'=>'{update}{delete}{restore}',
            'buttons' => [
                'delete' => function($url, $model, $key) {
                    $options = [
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'data-pjax' => '0',
                    ];
                    if ($model->identity->active)
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                    else
                        return null;
                },
                'restore' => function($url, $model, $key) {
                    $options = [];
                    if (!$model->identity->active)
                        return Html::a('<span class="glyphicon glyphicon-check"></span>', $url, $options);
                    else
                        return null;
                }
            ],
            'headerOptions' => [
                'width' => '75px'
            ]
        ]
    ],
    'tableOptions' => ['class' => 'table table-bordered'],
    'rowOptions' => function($model, $key, $index, $grid) {
        if ($model->identity->active)
            return ['class' => 'active-user-row'];
        else
            return ['class' => 'inactive-user-row'];
    }
]);

