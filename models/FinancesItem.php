<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 11.04.2017
 * Time: 15:20
 */

namespace app\models;

use yii\base\Model;

class FinancesItem extends Model
{
    public $time;
    public $cash;
    public $description;

    public function attributeLabels()
    {
        return [
            'time' => 'Время',
            'cash' => 'Сумма',
            'description' => 'Описание',
        ];
    }
}