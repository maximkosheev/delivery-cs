<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 16.12.2016
 * Time: 11:30
 */

namespace app\models;

use yii\base\Model;

/**
 * Class DeliverymanStatistic
 * @package app\models
 */
class DeliverymanStatistic extends Model
{
    /** @var  integer Всего выполненных доставок  */
    public $totalCount;
    /** @var  integer Выполненых в данном месяце  */
    public $mounthCount;
    /** @var  integer Всего успешно выполненых  */
    public $successfulCount;
    /** @var  integer Всего опозданий  */
    public $lateCount;
}