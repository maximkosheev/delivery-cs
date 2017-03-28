<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 27.02.2017
 * Time: 23:06
 * @var \yii\web\View $this
 * @var \app\models\Manager $employer
 */

use yii\helpers\Html;
?>

<table class="table table-striped table-bordered detail-view">
    <tr>
        <th>
            ФИО
        </th>
        <td>
            <?= Html::encode($employer->fio) ?>
        </td>
    </tr>
    <tr>
        <th>
            Телефон
        </th>
        <td>
            <?= Html::encode($employer->phone) ?>
        </td>
    </tr>
    <tr>
        <th>
            Паспортные данные
        </th>
        <td>
            <?= Html::encode($employer->passport) ?>
        </td>
    </tr>
    <tr>
        <th>
            Фото
        </th>
        <td>
            <?php
            if (!empty($model->photo))
                echo Html::img($model->photo, ['class' => 'worker-photo']);
            ?>
        </td>
    </tr>
</table>
