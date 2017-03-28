<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 26.12.2016
 * Time: 9:33
 */

namespace app\models;

use yii\base\Model;

class HistoryForm extends Model
{
    public $dateFrom;
    public $dateTo;
    public $manager_id;
    public $deliveryman_id;
    public $status;

    public function rules()
    {
        return [
            [['dateFrom', 'dateTo', 'manager_id', 'deliveryman_id', 'status'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'dateFrom' => 'Начальная дата',
            'dateTo' => 'Конечная дата',
            'manager_id' => 'Менеджер',
            'deliveryman_id' => 'Курьер',
            'status' => 'Статус'
        ];
    }
}