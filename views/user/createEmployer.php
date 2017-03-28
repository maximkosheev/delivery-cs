<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 15.12.2016
 * Time: 16:00
 * @var \app\models\Worker $model
 * @var \yii\web\View $this
 */

use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

if ($model instanceof \app\models\Manager)
    $this->title = 'Новый менеджер';
else if ($model instanceof \app\models\Deliveryman)
    $this->title = 'Новый курьер';

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $this->title],
    ]
]);

$actionFailedMessage = Yii::$app->session->getFlash('actionFailed', null);

if ($actionFailedMessage !== null) {
    echo '<div class="alert alert-danger">'.$actionFailedMessage.'<br>'.'</div>';
}

$form = ActiveForm::begin([
    'id' => 'user-form'
]);

echo $form->field($model, 'username')->textInput();
echo $form->field($model, 'password')->passwordInput();
echo $form->field($model, 'password_repeat')->passwordInput();
echo $form->field($model, 'fio')->textInput();
echo $form->field($model, 'phone')->textInput()->label('Телефон (вводиться без 8 и без +7)');
echo $form->field($model, 'passport')->textarea();
echo $form->field($model, 'file')->fileInput();

if ($model instanceof \app\models\Deliveryman)
    echo $this->render('_packageTypes', [
        'allPackageTypes' => $allPackageTypes,
        'allowedPackageTypes' => null,
    ]);
echo Html::submitButton('Создать');
ActiveForm::end();
