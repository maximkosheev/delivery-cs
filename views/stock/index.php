<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 17.04.2017
 * Time: 12:18
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Stock $searchModel
 * @var \app\models\Stock $stock
 */

use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use app\components\widgets\ActionStatusMessage;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Свой склад';

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => $this->title],
    ]
]);

echo '<div id="actionMessage">';
echo ActionStatusMessage::widget([]);
echo '</div>';

Pjax::begin(['id' => 'new-item-form']);
    $form = ActiveForm::begin(['id' => 'stock_form', 'options' => ['data-pjax' => true]]);
?>
    <div style="margin-bottom: 34pt">
        <table style="margin-bottom: 6pt">
            <tr>
                <td style="padding-right: 10px"><?=$form->field($stock, 'id')->textInput();?></td>
                <td style="padding-right: 10px"><?=$form->field($stock, 'brand')->textInput();?></td>
                <td style="padding-right: 10px"><?=$form->field($stock, 'description')->textInput();?></td>
                <td style="padding-right: 10px"><?=$form->field($stock, 'purchase_price')->textInput();?></td>
                <td style="padding-right: 10px"><?=$form->field($stock, 'owner')->textInput();?></td>
            </tr>
        </table>
        <?=Html::submitButton('Добавить', ['class' => 'btn btn-lg btn-success']); ?>
    </div>
<?php
    ActiveForm::end();
Pjax::end();

$this->registerJs(
    '$("document").ready(function(){
        $("#new-item-form").on("pjax:end", function(){
            $.pjax.reload({container:"#stock-items"});
        })
    })'
);

Pjax::begin(['id' => 'stock-items']);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'emptyText' => 'Ничего не найдено',
    'summary' => 'Показано <b>{begin, number}-{end, number}</b> из <b>{totalCount, number}</b> {totalCount, plural, one{товар} other{товаров}}.',
    'columns' => [
        'id',
        'brand',
        'description',
        'purchase_price',
        'owner',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function($url, $model, $key){
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                        'class' => 'ajaxDelete',
                        'title' => Yii::t('yii', 'Delete'),
                        'url' => $url,
                        'pjax-container' => 'stock-items'
                    ]);
                }
            ]
        ]
    ]
]);
Pjax::end();