<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 17.04.2017
 * Time: 12:03
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Stock
 * @package app\models
 * @property string $id - артикул
 * @property string $brand - бренд
 * @property string $description - наименование/описание
 * @property float $purchase_price - цена закупки
 * @property string $owner - владелец
 * @property
 */
class Stock extends ActiveRecord
{
    public function rules()
    {
        return [
            ['id', 'required', 'message' => 'Поле не может быть пустым'],
            ['purchase_price', 'double', 'message' => 'Поле должно быть числом с десятичной точкой'],
            [['brand', 'description', 'owner'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Артикул',
            'brand' => 'Бренд',
            'description' => 'Наименование',
            'purchase_price' => 'Цена закупки',
            'owner' => 'У кого',
        ];
    }
}