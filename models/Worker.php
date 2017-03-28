<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 08.12.2016
 * Time: 10:54
 */

namespace app\models;

use yii\base\NotSupportedException;
use yii\db\ActiveRecord;

/**
 * Class Worker
 * @package app\models
 * @property integer $user_id
 * @property string $fio
 * @property string $phone
 * @property string $passport
 * @property string $photo
 */
class Worker extends ActiveRecord
{
    public $username;
    public $password;
    public $password_repeat;
    public $file;

    public function rules()
    {
        return [
            [['username', 'password', 'password_repeat'], 'required', 'message' => 'Поле не может быть пустым', 'on'=>'insert'],
            [['fio', 'phone'], 'required', 'message' => 'Поле не может быть пустым'],
            ['password', 'string', 'min'=>6, 'message'=>'Минимальная длина пароля 6 символов'],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'message'=>'Пароли должны совпадать', 'on'=>'insert'],
            ['file', 'file', 'extensions' => ['jpg', 'jpeg', 'png']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Имя пользователя',
            'password' => 'Пароль',
            'password_repeat' => 'Пароль еще раз',
            'fio' => 'Фамилия Имя Отчество',
            'phone' => 'Телефон',
            'passport' => 'Паспортные данные',
            'file' => 'Фото'
        ];
    }

    public function getIdentity()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            // сохраняем идентификацию пользователя
            $identity = new User();
            $identity->username = $this->username;
            $identity->password = md5($this->password);
            $identity->active = 1;
            $identity->token = 'bla-bla-bla';
            if ($identity->save()) {
                $this->user_id = $identity->id;
            }
            else {
                return false;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @param integer $id
     * @return Worker|null
     */
    public static function findit($id)
    {
        $worker = Manager::find()
            ->with('identity')
            ->where(['user_id' => $id])
            ->one();

        if ($worker === null)
            $worker = Deliveryman::find()
                ->with(['identity', 'packageTypes'])
                ->where(['user_id' => $id])
                ->one();

        return $worker;
    }

    public static function getIndexUrl()
    {
        throw new NotSupportedException();
    }
}
