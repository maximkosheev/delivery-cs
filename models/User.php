<?php

namespace app\models;

use app\common\behaviors\GetRoleBehavior;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use yii\db\ActiveRecord;

/**
 * Class User
 * @package app\models
 * @property integer $id
 * @property integer $active
 * @property string $username
 * @property string $password
 * @property string $token
 */

class User extends ActiveRecord implements IdentityInterface
{
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_DELIVERYMAN = 'deliveryman';

    public $role;

    public function behaviors()
    {
        return [
            'getRole' => [
                'class' => GetRoleBehavior::className()
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return self::findOne(['username' => $username]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === md5($password);
    }

    /**
     * ��������� ���������� ������������
     */
    public function block()
    {
        $this->setAttribute('active', 0);
        return $this->save(false, ['active']);
    }

    public function unblock()
    {
        $this->setAttribute('active', 1);
        return $this->save(false, ['active']);
    }

    public function getIsAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function getIsManager()
    {
        return $this->role === self::ROLE_ADMIN || $this->role === self::ROLE_MANAGER;
    }

    public function getIsDeliveryman()
    {
        return $this->role === self::ROLE_DELIVERYMAN;
    }

    public function registerToken($token)
    {
        if ($this->token != $token) {
            $this->token = $token;
            return $this->save(false, ['token']);
        }
        else
            return true;
    }

    public function unregisterToken($token)
    {
        $this->token = '';
        return $this->save(false, ['token']);
    }
}
