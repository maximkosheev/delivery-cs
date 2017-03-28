<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 15.02.2017
 * Time: 10:47
 */

namespace app\models;

use yii\base\Model;

class ChangePasswordForm extends Model
{
    public $password;
    public $password_repeat;

    public function rules()
    {
        return [
            [['password', 'password_repeat'], 'required', 'message' => 'Поле не может быть пустым'],
            ['password', 'string', 'min'=>6, 'message'=>'Минимальная длина пароля 6 символов'],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'message'=>'Пароли должны совпадать'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Пароль',
            'password_repeat' => 'Пароль еще раз'
        ];
    }
}