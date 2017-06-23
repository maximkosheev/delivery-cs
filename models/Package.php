<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 12.12.2016
 * Time: 12:12
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Package
 * @package app\models
 * @property integer $id
 * @property integer $deliveryman_id
 * @property integer $manager_id
 * @property integer $status
 * @property integer $package_type
 * @property string $address_from
 * @property string $address_to
 * @property string $phone_from
 * @property string $phone_to
 * @property string $model
 * @property string $delivery_type
 * @property string $more
 * @property string $cost
 * @property string $purchase_price
 * @property string $selling_price
 * @property string $create_time
 * @property string $open_time
 * @property string $close_time
 * @property string $deadline_time
 */
class Package extends ActiveRecord
{
    // исполнитель не опререлен
    const STATUS_NOT_APPLIED	= 0;
    // исполнительно определен
    const STATUS_APPLIED 		= 1;
    // курьер забрал посылку
    const STATUS_PICKUP			= 2;
    // получатель отказался от посылки
    const STATUS_CANCELED		= 3;
    // посылка доставлена (заявка завершена)
    const STATUS_DELIVERED		= 4;
    // посылка возвращена (заявка завершена)
    const STATUS_BACKOFF        = 5;
    // это фиктивный статус (работа над заявкой продолжается)
    const STATUS_OPEN           = 12;

    const COLOR_NOT_APPLIED		= 0xFF0000;
    const COLOR_APPLIED			= 0x00FF00;
    const COLOR_PICKUP			= 0x0000FF;
    const COLOR_DELIVERED		= 0xAAAAAA;
    const COLOR_CANCELED        = 0xDDDDDD;
    const COLOR_BACKOFF			= 0xCCCCCC;

    public $deliveryTypesArray;

    public function rules()
    {
        return [
            [['package_type', 'model'], 'required', 'message' => 'Поле не может быть пустым'],
            [['address_from', 'address_to'], 'string', 'max'=>255, 'message' => 'Длина не может превышать 255 символов'],
            [['phone_from', 'phone_to'], 'string', 'max'=>20, 'message' => 'Длина не может превышать 20 символов'],
            [['model', 'delivery_type'], 'string', 'max'=>50, 'message' => 'Длина не может превышать 50 символов'],
            [['cost', 'purchase_price', 'selling_price'], 'number', 'integerOnly' => true, 'message' => 'Поле должно быть целым числом'],
            [['status', 'deliveryman_id', 'create_time', 'open_time', 'close_time', 'deadline_time', 'deliveryTypes', 'more'], 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            // Выставляем статус в зависимости от выбран или нет курьер
            if ($this->deliveryman_id == null)
                $this->status = self::STATUS_NOT_APPLIED;
            else
                $this->status = self::STATUS_APPLIED;
            $this->manager_id = \Yii::$app->user->id;
            $this->create_time = date('Y-m-d H:i:s', time());
            $this->open_time = $this->status === self::STATUS_APPLIED ? date('Y-m-d H:i:s', time()) : null;
            $this->close_time = null;
            $this->deadline_time = date('Y-m-d H:i:s', time() + 14400);
        }

        if ($this->status === self::STATUS_APPLIED) {
            $this->close_time = null;
        }

        if ($this->status === self::STATUS_BACKOFF) {
            $this->close_time = date('Y-m-d H:i:s', time());
        }

        if ($this->deliveryman_id == null) {
            $this->status = self::STATUS_NOT_APPLIED;
            $this->open_time = null;
            $this->close_time = null;
        }

        if (!empty($this->deliveryTypes))
            $this->delivery_type = implode(',', $this->deliveryTypes);

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (empty($this->close_time) && ($this->status == self::STATUS_DELIVERED || $this->status == self::STATUS_BACKOFF))
            throw new \Exception('close_time attribute is not set while status is closed');
    }

    public function getStatusAllowRange()
    {
        return [
            self::STATUS_NOT_APPLIED,
            self::STATUS_APPLIED,
            self::STATUS_PICKUP,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELED,
            self::STATUS_BACKOFF,
        ];
    }

    public function getStatusOptions()
    {
        return [
            self::STATUS_NOT_APPLIED => 'Исполнитель не определен',
            self::STATUS_APPLIED => 'Исполнитель определен',
            self::STATUS_PICKUP => 'Забрал',
            self::STATUS_DELIVERED => 'Доставлена',
            self::STATUS_CANCELED => 'На возврат',
            self::STATUS_BACKOFF => 'Возвращена'
        ];
    }

    public function getStatusText()
    {
        return isset($this->statusOptions[$this->status]) ? $this->statusOptions[$this->status] : "Неизвестный ({$this->status})";
    }

    public function getStatusColor()
    {
        switch($this->status) {
            case self::STATUS_NOT_APPLIED:
                return self::COLOR_NOT_APPLIED;
            case self::STATUS_APPLIED:
                return self::COLOR_APPLIED;
            case self::STATUS_PICKUP:
                return self::COLOR_PICKUP;
            case self::STATUS_DELIVERED;
                return self::COLOR_DELIVERED;
            case self::STATUS_CANCELED:
                return self::COLOR_CANCELED;
            case self::STATUS_BACKOFF:
                return self::COLOR_BACKOFF;
        }
    }

    public function getStatusCssClass()
    {
        switch($this->status) {
            case self::STATUS_NOT_APPLIED:
                return 'not_applied';
            case self::STATUS_APPLIED:
                return 'applied';
            case self::STATUS_PICKUP:
                return 'pickup';
            case self::STATUS_DELIVERED;
                return 'delivered';
            case self::STATUS_CANCELED:
                return 'canceled';
            case self::STATUS_BACKOFF:
                return 'backoff';
        }
    }

    public function attributeLabels()
    {
        return [
            'status' => 'Статус заявки',
            'manager_id' => 'Менеджер',
            'deliveryman_id' => 'Курьер',
            'package_type' => 'Тип посылки',
            'address_from' => 'Откуда забрать',
            'address_to' => 'Куда доставить',
            'phone_from' => 'Телефон откуда забрать',
            'phone_to' => 'Телефон куда доставить',
            'model' => 'Марка',
            'delivery_type' => 'Вариант доставки',
            'deliveryTypes' => 'Вариант доставки',
            'more' => 'Подробности',
            'cost' => 'Стоимость',
            'purchase_price' => 'Цена закупки',
            'selling_price' => 'Цена продажи',
            'create_time' => 'Создана',
            'open_time' => 'Открыта',
            'close_time' => 'Закрыта',
            'deadline_time' => 'Срок окончания'
        ];
    }

    public function getPackageType()
    {
        return $this->hasOne(PackageType::className(), ['id' => 'package_type']);
    }

    public function getDeliveryTypes()
    {
        if ($this->deliveryTypesArray === null)
            $this->deliveryTypesArray = explode(',', $this->delivery_type);
        return $this->deliveryTypesArray;
    }

    public function setDeliveryTypes($value)
    {
        $this->deliveryTypesArray = $value;
    }

    public function getDeliveryTypeOptions()
    {
        return [
            'type1' => 'Пеший курьер',
            'type2' => 'Водитель на машине'
        ];
    }

    public function getManager()
    {
        return $this->hasOne(Manager::className(), ['user_id' => 'manager_id']);
    }

    public function getDeliveryman()
    {
        return $this->hasOne(Deliveryman::className(), ['user_id' => 'deliveryman_id']);
    }

    public function getIsVacant()
    {
        if ($this->status === self::STATUS_NOT_APPLIED && $this->deliveryman_id === null)
            return true;
        else
            return false;
    }

    public function undertake($userid)
    {
        $this->deliveryman_id = $userid;
        $this->status = self::STATUS_APPLIED;
        $this->open_time = date('Y-m-d H:i:s', time());
        return $this->save(true, ['deliveryman_id', 'status', 'open_time']);
    }

    public function close()
    {
        if ($this->status > self::STATUS_NOT_APPLIED && $this->status < self::STATUS_DELIVERED)
        $this->status = self::STATUS_DELIVERED;
        $this->close_time = date('Y-m-d H:i:s', time());
        return $this->save(false, ['status', 'close_time']);
    }
}