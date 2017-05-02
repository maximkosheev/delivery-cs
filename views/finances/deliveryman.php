 <?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 02.04.2017
 * Time: 14:56
 * @var \yii\web\View $this
 * @var \app\models\Deliveryman $deliveryman
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\FinancesFilter $filterModel
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
 use yii\widgets\ActiveForm;
 use yii\jui\DatePicker;

$this->title = $deliveryman->fio;

echo Breadcrumbs::widget([
    'homeLink' => [
        'label' => 'Главная',
        'url' => ['/']
    ],
    'links' => [
        ['label' => 'Финансы', 'url' => Url::to(['finances/deliverymans'])],
        ['label' => $this->title],
    ]
]);

echo '<h2>'."Курьер - $deliveryman->fio".'</h2>';
echo '<h3>Баланс: '.$deliveryman->balance.'</h3>';
echo Html::a('Отправить деньги', ['finances/receipt', 'id' => $deliveryman->user_id], ['class' => 'btn btn-lg btn-success']);

 $form = ActiveForm::begin(['id' => 'filter-form']);
 echo '<h2>Фильтр по датам создания заявок:</h2>';
 echo $form->field($filterModel, 'dateFrom')->widget(DatePicker::className(), [
     'dateFormat' => 'dd-MM-yyyy',
     'options' => [
         'class' => 'form-control',
     ]
 ]);
 echo $form->field($filterModel, 'dateTo')->widget(DatePicker::className(), [
     'dateFormat' => 'dd-MM-yyyy',
     'options' => [
         'class' => 'form-control',
     ]
 ]);
 echo '<br>';
 echo Html::submitButton('Применить фильтр', ['class' => 'btn btn-lg btn-success']);
 ActiveForm::end();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'emptyText' => 'Ничего не найдено',
    'summary' => 'Показано <b>{begin, number}-{end, number}</b> из <b>{totalCount, number}</b> {totalCount, plural, one{запись} other{записей}}.',
    'columns' => [
        [
            'attribute' => 'time',
            'headerOptions' => [
                'width' => '200px'
            ]
        ],
        'cash',
        'description'
    ],
]);
