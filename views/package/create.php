<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 13.12.2016
 * Time: 11:11
 * @var \yii\web\View $this
 * @var \app\models\Package $package
 * @var \yii\data\ActiveDataProvider $lastPackages
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use yii\grid\GridView;
use app\components\widgets\ActionStatusMessage;

$this->title = 'Новая заявка';

$this->params['menuItems'] = require(__DIR__.'/_menuItems.php');

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $this->title],
    ]
]);

$form = ActiveForm::begin([
    'id' => 'package-form'
]);

$script = <<< SCRIPT
$(function(){
    $.ajax({
        url:"/?r=package/deliverymans&packageType="+$("#package_type").val(),
        success: function(data) {
            $("#package_deliverymans").empty();
            var deliverymans = $.parseJSON(data);
            if (deliverymans.length != 0) {
                $("#package_deliverymans").append(
                    $("<option></option>").val("").html("Выберете курьера")
                )
                $.each(deliverymans, function(index, element) {
                    $("#package_deliverymans").append(
                        $("<option></option>").val(index).html(element)
                    )
                });
            }
            else {
                $("#package_deliverymans").append(
                    $("<option></option>").val("").html("Курьеры не найдены")
                )
            }
        }
    });
});
SCRIPT;

$this->registerJs($script, \yii\web\View::POS_READY);

echo ActionStatusMessage::widget([]);
?>


<table width="100%" class="request-data">
    <thead>
        <tr>
            <td width="50%">
            </td>
            <td width="50%">

            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?=$form->field($package, 'address_from')->textarea(); ?>
            </td>
            <td>
                <?= $form->field($package, 'address_to')->textarea(); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($package, 'phone_from')->textInput(); ?>
            </td>
            <td>
                <?= $form->field($package, 'phone_to')->textInput(); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($package, 'model')->textInput();?>
                <div class="form-group">
                    <?php
                    foreach ($models['cars'] as $car) {
                        echo '<span class="model-item">'.$car.'</span>';
                    }
                    echo '<br>';
                    foreach ($models['trucks'] as $truck) {
                        echo '<span class="model-item">'.$truck.'</span>';
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($package, 'package_type', ['options' => ['style'=>'display: none;']])->dropDownList($types, [
                    'id' => 'package_type',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($package, 'purchase_price', ['options' => ['class'=>'price-field']])->textInput(); ?>
                <?= $form->field($package, 'selling_price', ['options' => ['class'=>'price-field']])->textInput(); ?>
                <?= $form->field($package, 'cost', ['options' => ['class'=>'price-field']])->textInput(); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($package, 'deliveryman_id')->dropDownList([], [
                    'id' => 'package_deliverymans',
                    'data-url' => Url::to(['package/deliverymans']),
                    'prompt' => 'Выберете курьера'
                ]);?>
            </td>
            <td>
                <?= $form->field($package, 'deliveryTypes')->checkboxList(
                    $package->getDeliveryTypeOptions(),
                    ['itemOptions' => [
                        'labelOptions' => ['style' => 'display: block;']
                    ]]
                ); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($package, 'more')->textarea(); ?>
            </td>
        </tr>
    </tbody>
</table>
<?= Html::submitButton('Сохранить') ?>

<?php ActiveForm::end(); ?>

<h2>Сегодняшние заявки</h2>
<?php
echo GridView::widget([
    'dataProvider' => $lastPackages,
    'emptyText' => 'Сегодня заявок еще не было',
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
            'contentOptions' => [
                'contenteditable' => 'true',
                'onchange' => 'setCost($(this).closest("tr").attr("data-key"), $(this).data("before"))',
            ]
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
