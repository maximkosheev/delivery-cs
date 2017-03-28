<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 13.12.2016
 * Time: 11:11
 * @var \yii\web\View $this
 * @var \app\models\Package $package
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\components\widgets\ActionStatusMessage;
use app\models\Package;

$this->title = 'Редактирование заявки #'.$package->id;

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

echo ActionStatusMessage::widget([]);

$form = ActiveForm::begin([
    'id' => 'package-form'
]);

$script = <<< SCRIPT
$(function(){
    $.ajax({
        url:"/?r=package/deliverymans&packageType="+$("#package_type").val(),
        success: function(data) {
            var currentDeliverymanId = $("#package_deliverymans").val();
            var deliverymans = $.parseJSON(data);
            $.each(deliverymans, function(index, element) {
                if (index != currentDeliverymanId) {
                    $("#package_deliverymans").append(
                        $("<option></option>").val(index).html(element)
                    )
                }
            });
            $("#package_deliverymans").val(currentDeliverymanId);
        }
    });
});
SCRIPT;

$this->registerJs($script, \yii\web\View::POS_READY);
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
                <div class="form-group">
                    <?= $form->field($package, 'model')->textInput();?>
                    <?php
                    foreach ($models['cars'] as $car) {
                        if ($package->model === $car)
                            echo '<span class="model-item active">'.$car.'</span>';
                        else
                            echo '<span class="model-item">'.$car.'</span>';
                    }
                    echo '<br>';
                    foreach ($models['trucks'] as $truck) {
                        if ($package->model === $truck)
                            echo '<span class="model-item active">'.$truck.'</span>';
                        else
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
        <tr>
            <td>
                <?= $form->field($package, 'purchase_price', ['options' => ['class'=>'price-field']])->textInput(); ?>
                <?= $form->field($package, 'selling_price', ['options' => ['class'=>'price-field']])->textInput(); ?>
                <?= $form->field($package, 'cost', ['options' => ['class'=>'price-field']])->textInput(); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($package, 'deliveryman_id')->dropDownList($package->deliveryman !== null ? [$package->deliveryman_id => $package->deliveryman->fio] : [], [
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
        <tr>
            <td>
                <?php
                // статус можно менять только у открытой заявки (та, у которой определен исполнитель)
                if ($package->deliveryman_id !== null)
                    echo $form->field($package, 'status')->dropDownList([
                        Package::STATUS_PICKUP => 'Забрал',
                        Package::STATUS_CANCELED => 'На возврат',
                        Package::STATUS_DELIVERED => 'Доставил',
                        Package::STATUS_BACKOFF => 'Возврат'
                    ], ['prompt' => 'Если статус изменился...']);
                ?>
            </td>
        </tr>
    </tbody>
</table>
<?= Html::submitButton('Сохранить') ?>

<?php ActiveForm::end(); ?>