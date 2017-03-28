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

$ajaxDeleteScript = <<< JS
    $(document).on('ready pjax:success', function() {
        $('.ajaxDelete').on('click', function(event) {
            event.preventDefault();
            var deleteUrl = $(this).attr('url');
            var pjaxContainer = $(this).attr('pjax-container');
            if (confirm('Выдействительно хотите удалить данную заявку?')) {
                $.ajax({
                    url: deleteUrl,
                    type: 'post',
                    error: function(xhr, status, error) {
                        $('#actionMessage').html('<div class="alert alert-failed">Заявка не удалена из-за неполадок на сервере</div>');
                    }
                }).done(function(data){
                    $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                    $('#actionMessage').html('<div class="alert alert-success">Заявка успешно удалена</div>');
                });
            }
        })
    });
JS;


echo '<div id="actionMessage">';
echo ActionStatusMessage::widget([]);
echo '</div>';

$this->registerJs($ajaxDeleteScript);

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
        <td><?= $form->field($model, 'manager_id')->dropDownList($managers, ['prompt'=>'Выберете менеджера']); ?></td>
        <td><?= $form->field($model, 'deliveryman_id')->dropDownList($deliverymans, ['prompt'=>'Выберете курьера']);?></td>
    </tr>
</table>

<?php
echo Html::submitButton('Отобразить');
ActiveForm::end();

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
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'cost',
            'visible' => Yii::$app->user->identity->isAdmin,
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
            'template' =>  Yii::$app->user->identity->isManager ? '{view}{update}{delete}' : '{view}',
            'buttons' => [
                'delete' => function($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                        'class' => 'ajaxDelete',
                        'title' => Yii::t('yii', 'Delete'),
                        'url' => $url,
                        'pjax-container' => 'packages'
                    ]);
                }
            ],
            'headerOptions' => [
                'width'=>'70px'
            ]
        ]
    ],
    'rowOptions' => function($model, $key, $index, $grid) {
        return ['class' => $model->statusCssClass];
    }
]);
Pjax::end();
?>
