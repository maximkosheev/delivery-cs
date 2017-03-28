<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 08.12.2016
 * Time: 12:42
 *
 * @var \yii\web\View $this
 * @var \app\models\Worker $model
 */

use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\components\widgets\ActionStatusMessage;

if ($model instanceof \app\models\Manager)
    $this->title = 'Редактирование менеджера';
else if ($model instanceof \app\models\Deliveryman)
    $this->title = 'Редактирование курьера';

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $this->title],
    ]
]);

if (Yii::$app->user->identity->isAdmin)
    $this->params['menuItems'] = [
        ['label'=>'Замена пароля', 'url'=>Url::to(['user/changepassword', 'id' => $model->user_id])],
    ];

echo ActionStatusMessage::widget([]);

$form = ActiveForm::begin([
    'id' => 'user-form'
]);

if (!empty($model->photo))
    echo Html::img($model->photo, ['class' => 'worker-photo']);

echo $form->field($model, 'fio')->textInput();
echo $form->field($model, 'phone')->textInput()->label('Телефон (вводиться без 8 и без +7)');
echo $form->field($model, 'passport')->textarea();
echo $form->field($model, 'file')->fileInput();

if ($model instanceof \app\models\Deliveryman) {
    echo $this->render('_packageTypes', [
        'allPackageTypes' => $allPackageTypes,
        'allowedPackageTypes' => ArrayHelper::map($model->packageTypes, 'id', 'id'),
    ]);
}
echo Html::submitButton('Сохранить');
ActiveForm::end();
