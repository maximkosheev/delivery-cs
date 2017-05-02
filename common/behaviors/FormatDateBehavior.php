<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 02.05.2017
 * Time: 10:34
 */

namespace app\common\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class FormatDateTimeBehavior
 * Выполняет преобразование даты к формату Y-m-d
 * @package app\common\behaviors
 */
class FormatDateBehavior extends Behavior
{
    public $srcAttr;
    public $destAttr;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'formatDate',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'formatDate'
        ];
    }

    public function formatDate()
    {
        $model = $this->owner;
        $dateTime = \DateTime::createFromFormat('d-m-Y', $model->{$this->srcAttr});
        $model->{$this->destAttr} = $dateTime->format('Y-m-d');
    }
}