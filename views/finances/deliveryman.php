 <?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 02.04.2017
 * Time: 14:56
 * @var \yii\web\View $this
 * @var \app\models\Deliveryman $deliveryman
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\models\FinancesLog;

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

echo '<h2>'.$deliveryman->fio.'</h2>';

echo Html::a('Отправить денег', ['finances/receipt', 'id' => $deliveryman->user_id], ['class' => 'btn btn-lg btn-success']);

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
