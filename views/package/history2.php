<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 26.12.2016
 * Time: 9:31
 * @var \yii\web\View $this
 * @var \app\models\HistoryForm $model
 * @var \app\models\Deliveryman array $deliverymans
 * @var \yii\data\ActiveDataProvider $packages
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use app\components\widgets\ActionStatusMessage;

$this->title = 'История заявок';

$this->params['menuItems'] = require(__DIR__.'/_menuItems.php');

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => 'Заявки', 'url' => Url::to(['package/index'])],
        ['label' => $this->title],
    ]
]);


$form = ActiveForm::begin([
    'id' => 'history-form',
    'method' => 'get'
]);

?>

<table id="history-config" width="100%">
    <tr>
        <td><?= $form->field($model, 'dateFrom')->widget(DatePicker::className(), ['dateFormat' => 'dd-MM-yyyy']);?></td>
        <td><?= $form->field($model, 'dateTo')->widget(DatePicker::className(), ['dateFormat' => 'dd-MM-yyyy']);?></td>
    </tr>
    <tr>
        <td><?= $form->field($model, 'status')->dropDownList([
                \app\models\Package::STATUS_OPEN => 'Открытые',
                \app\models\Package::STATUS_DELIVERED => 'Доставленные',
                \app\models\Package::STATUS_BACKOFF => 'Возврат'
            ], ['prompt'=>'Выберете статус']); ?></td>
    </tr>
</table>

<?php
echo Html::submitButton('Отобразить');
ActiveForm::end();

echo GridView::widget([
    'dataProvider' => $packages,
    'emptyText' => 'В данной категории заявок нет',
    'summary' => 'Показано <b>{begin, number}-{end, number}</b> из <b>{totalCount, number}</b> {totalCount, plural, one{заявка} other{заявок}}.',
    'tableOptions' => ['class' => 'table table-bordered'],
    'columns' => [
        'create_time',
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'status',
            'value' => function(\app\models\Package $data) {
                return $data->statusText;
            }
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
        'purchase_price',
        'selling_price',
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'deliveryman_id',
            'value' => function(\app\models\Package $data) {
                if ($data->deliveryman !== null)
                    return Html::encode($data->deliveryman->fio);
                else
                    return 'Исполнитель не определен';
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' =>  '{view}',
            'headerOptions' => [
                'width'=>'70px'
            ]
        ]
    ],
    'rowOptions' => function($model, $key, $index, $grid) {
        return ['class' => $model->statusCssClass];
    }
]);
?>
