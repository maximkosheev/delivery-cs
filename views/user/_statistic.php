<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 16.12.2016
 * Time: 14:13
 * @var \app\models\DeliverymanStatistic $statistic
 */
?>
Всего выполненых доставок: <?=$statistic->totalCount?> <br>
Всего выполненых доставок за последний месяц: <?=$statistic->mounthCount?> <br>
Всего успешных доставок: <?=$statistic->successfulCount?> <br>
Всего опоздал: <?=$statistic->lateCount?> <br>

