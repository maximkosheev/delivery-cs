<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 01.04.2017
 * Time: 9:31
 */

namespace app\models;

use app\common\behaviors\FormatDateBehavior;
use app\common\behaviors\SetDateTimeBehavior;
use yii\db\ActiveRecord;

/**
 * Class FinancesLog
 * @package app\models
 * @property string $time
 * @property int $deliveryman_id
 * @property float $cash
 * @property string $description
 */
class FinancesLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'tbl_finances_log';
    }

    public function rules()
    {
        return [
            [['deliveryman_id', 'type', 'time'], 'required', 'message'=>'Поле не может быть пустым'],
            ['cash', 'double', 'message'=>'Поле должно быть числом'],
            [['description'], 'safe']
        ];
    }

    public function behaviors()
    {
        return [
            'formatDate' => [
                'class' => FormatDateBehavior::className(),
                'srcAttr' => 'time',
                'destAttr' => 'time'
            ]
        ];
    }

    public function getDeliveryman()
    {
        return $this->hasOne(Deliveryman::className(), ['user_id' => 'deliveryman_id']);
    }

    public function attributeLabels()
    {
        return [
            'time' => 'Время',
            'deliveryman_id' => 'Курьер',
            'cash' => 'Сумма',
            'description' => 'Описание'
        ];
    }
}