<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 31.03.2017
 * Time: 11:54
 * @var \yii\web\View $this
 * @var \app\models\ReceiptForm $model
 * @var array $deliverymans
 */

use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use app\components\widgets\ActionStatusMessage;

$this->title = 'Отправка денежных средств курьеру';

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $this->title],
    ]
]);

echo ActionStatusMessage::widget([]);

$form = ActiveForm::begin([
    'id' => 'receipt-form'
]);

echo $form->field($model, 'deliveryman_id')->dropDownList($deliverymans, ['prompt' => 'Выберете курьера']);
echo $form->field($model, 'cash')->textInput();
echo '<br>';
echo Html::submitButton('Отправить', ['class' => 'btn btn-lg btn-success']);

ActiveForm::end();