<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 31.03.2017
 * Time: 11:38
 */

namespace app\models;

use yii\base\Model;

class ReceiptForm extends Model
{
    public $deliveryman_id;
    public $cash;
    public $time;
    public $description;

    public function rules()
    {
        return [
            [['deliveryman_id', 'cash', 'time'], 'required', 'message'=>'Поле не может быть пустым'],
            ['cash', 'number', 'integerOnly'=>true, 'message'=>'Поле должно целым числом'],
            ['description', 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'deliveryman_id' => 'Курьер',
            'cash' => 'Сумма',
            'time' => 'Дата',
            'description' => 'Описание'
        ];
    }
}