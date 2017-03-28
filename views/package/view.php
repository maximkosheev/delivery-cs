<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 20.12.2016
 * Time: 9:58
 * @var \yii\web\View $this
 * @var \app\models\Package $package
 */

use app\models\Package;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = "Заявка #".$package->id;

$this->params['menuItems'] = require(__DIR__.'/_menuItems.php');

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => 'Заявки', 'url' => Url::to(['package/index'])],
        ['label' => $this->title],
    ]
]);

?>


<table class="table table-striped table-bordered detail-view">
    <tr class="<?=$package->getStatusCssClass()?>">
        <th>
            Статус
        </th>
        <td>
            <?= Html::encode($package->statusText); ?>
        </td>
    </tr>
    <tr>
        <th>Откуда забрать</th>
        <td>
            <?= Html::encode($package->address_from)?>
            <br>
            <?= Html::encode($package->phone_from)?>
        </td>
    </tr>
    <tr>
        <th>Куда доставить</th>
        <td>
            <?=Html::encode($package->address_to)?>
            <br>
            <?= Html::encode($package->phone_to)?>
        </td>
    </tr>
    <tr>
        <th>Подробности</th>
        <td>
            <?php
                $more = str_replace("\r", '<br>', $package->more);
                echo $more;
            ?>
        </td>
    </tr>
    <tr>
        <th>Цена закупки</th>
        <td>
            <?=Html::encode($package->purchase_price)?>
        </td>
    </tr>
    <tr>
        <th>Цена продажи</th>
        <td>
            <?=Html::encode($package->selling_price)?>
        </td>
    </tr>
    <?php if (Yii::$app->user->identity->isAdmin) : ?>
    <tr>
        <th>Стоимость доставки</th>
        <td>
            <?=Html::encode($package->cost)?>
        </td>
    </tr>
    <?php endif ?>
    <tr>
        <th>Менеджер</th>
        <td>
            <?= $package->manager != null ? Html::encode($package->manager->fio) : 'Администратор';?>
            <br>
            <?=$package->create_time?>
        </td>
    </tr>
    <tr>
        <th>Курьер</th>
        <td>
            <?php
            if ($package->deliveryman !== null) {
                echo Html::encode($package->deliveryman->fio);
                echo '<div>';
                // для курьера показываем кнопки
                if (Yii::$app->user->identity->isDeliveryman) {
                    if ($package->status === Package::STATUS_APPLIED) {
                            echo  Html::a("Забрал",
                                [
                                    'package/collectit',
                                    'id' => $package->id
                                ],
                                ['class' => 'btn btn-lg btn-primary btn-action']);
                        echo  Html::a("На возврат",
                            [
                                'package/canceled',
                                'id' => $package->id
                            ],
                            ['class' => 'btn btn-lg btn-primary btn-action']);
                        echo  Html::a("Доставил",
                            [
                                'package/delivered',
                                'id' => $package->id
                            ],
                            ['class' => 'btn btn-lg btn-primary btn-action']);
                    }
                    else if ($package->status === Package::STATUS_PICKUP) {
                        echo  Html::a("На возврат",
                            [
                                'package/canceled',
                                'id' => $package->id
                            ],
                            ['class' => 'btn btn-lg btn-primary btn-action']);
                        echo  Html::a("Доставил",
                            [
                                'package/delivered',
                                'id' => $package->id
                            ],
                            ['class' => 'btn btn-lg btn-primary btn-action']);
                    }
                    else if ($package->status === Package::STATUS_CANCELED) {
                        echo  Html::a("Вернул",
                            [
                                'package/backoff',
                                'id' => $package->id
                            ],
                            ['class' => 'btn btn-lg btn-primary btn-action']);
                    }
                }
                echo '</div>';
            }
            else {
                if (Yii::$app->user->identity->isManager) {
                    echo 'Исполнитель не определен';
                }
                else {
                    if ($package->status === Package::STATUS_NOT_APPLIED)
                        echo  Html::a("Подрядиться",
                            [
                                'package/undertake',
                                'id' => $package->id
                            ],
                            ['class' => 'btn btn-lg btn-primary']);
                }
            }

            ?>
        </td>
    </tr>
    <?php if ($package->status === Package::STATUS_DELIVERED) : ?>
    <tr>
        <th>Время закрытия заявки</th>
        <td>
            <?= $package->close_time?>
        </td>
    </tr>
    <?php endif ?>
    <tr>
        <th>Успеть до</th>
        <td>
            <?=$package->deadline_time?>
        </td>
    </tr>
</table>