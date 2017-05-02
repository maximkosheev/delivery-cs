<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 02.05.2017
 * Time: 10:56
 */

namespace app\models;

use yii\base\Model;

class FinancesFilter extends Model
{
    public $dateFrom;
    public $dateTo;

    public function rules()
    {
        return [
            [['dateFrom', 'dateTo'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'dateFrom' => 'Начальная дата',
            'dateTo' => 'Конечная дата'
        ];
    }
}