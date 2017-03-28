<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 13.12.2016
 * Time: 15:31
 * @var \yii\data\ActiveDataProvider $packages
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

Pjax::begin(['id' => 'packages']);

echo GridView::widget([
    'dataProvider' => $packages,
    'emptyText' => 'В данной категории заявок нет',
    'summary' => 'Показано <b>{begin, number}-{end, number}</b> из <b>{totalCount, number}</b> {totalCount, plural, one{заявка} other{заявок}}.',
    'tableOptions' => ['class' => 'table table-bordered'],
    'columns' => [
        'create_time',
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'manager_id',
            'value' => function(\app\models\Package $data) {
				if ($data->manager != null)
					return Html::encode($data->manager->fio);
				else
					return 'Администратор';
            },
        ],
        [
            'class' => 'yii\grid\DataColumn',
            'label' => 'Адрес + Телефон откуда',
            'value' => function(\app\models\Package $data) {
                return Html::encode($data->address_from).'<br>'.Html::encode($data->phone_from);
            },
            'format' => 'raw'
        ],
        [
            'class' => 'yii\grid\DataColumn',
            'label' => 'Адрес + Телефон куда',
            'value' => function(\app\models\Package $data) {
                return Html::encode($data->address_to).'<br>'.Html::encode($data->phone_to);
            },
            'format' => 'raw'
        ],
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'cost',
            'visible' => Yii::$app->user->identity->isAdmin,
        ],
        'purchase_price',
        'selling_price',
        'deadline_time',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' =>  Yii::$app->user->identity->isManager ? '{view}{update}{delete}' : '{view}',
            'buttons' => [
                'delete' => function($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                        'class' => 'ajaxDelete',
                        'title' => Yii::t('yii', 'Delete'),
                        'url' => $url,
                        'pjax-container' => 'packages',
                    ]);
                }
            ],
            'headerOptions' => [
                'width'=>'70px'
            ]
        ],
    ]]);
Pjax::end();