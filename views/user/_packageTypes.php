<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 08.12.2016
 * Time: 12:42
 *
 * @var \yii\web\View $this
 * @var \app\models\Deliveryman $model
 * @var array $allPackageTypes
 * @var array $allowedPackageTypes
 */

use yii\helpers\Html;
?>

<div class="form-group">
    <label class="control-label">Разрешенные типы доставок:</label>
    <?= Html::checkboxList('packageTypes', $allowedPackageTypes, $allPackageTypes, ['separator'=>'<br>']); ?>
</div>
