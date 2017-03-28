<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 15.02.2017
 * Time: 11:00
 * @var \yii\web\View $this
 * @var \app\models\User $user
 * @var \app\models\ChangePasswordForm $model
 */

use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use app\components\widgets\ActionStatusMessage;

use yii\helpers\Html;

$this->title = 'Изменение пароля';

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => 'Редактирование данных пользователя '.$user->username, 'url' => ['/user/update', 'id' => $user->id]],
        ['label' => $this->title],
    ]
]);

echo ActionStatusMessage::widget([]);

$form = ActiveForm::begin([
    'id' => 'password-form'
]);

echo $form->field($model, 'password')->passwordInput();
echo $form->field($model, 'password_repeat')->passwordInput();

echo Html::submitButton('Изменить');

ActiveForm::end();