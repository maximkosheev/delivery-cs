<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 13.12.2016
 * Time: 14:16
 * @var \yii\web\View $this
 */

use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use yii\jui\Dialog;
use app\components\widgets\ActionStatusMessage;


$this->title = 'Все заявки';
if (Yii::$app->user->identity->isDeliveryman)
    $this->title = 'Мои заявки';

$partial = '_all_packages';

if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'current':
            $this->title = 'Текущие';
            break;
        case 0:
            $this->title = 'Вакантные';
            $partial = '_vacant_packages';
            break;
        case 1:
            $this->title = 'Открытые';
            $partial = '_open_packages';
            break;
        case 3:
            $this->title = 'Закрытые';
            $partial = '_close_packages';
            break;
        case 4:
            $this->title = 'Возвраты';
            $partial = '_backoff_packages';
            break;
    }
}

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

Dialog::begin([
    'clientOptions'=>[
        'dialogClass' => 'mydialog',
        'autoOpen' => false,
        'modal' => true,
        'title' => 'Результат операции'
    ],
]);
 echo '<div>Стоимость заявки изменена</div>';

Dialog::end();

$setCostScript = <<< JS
	function setCost(id, value) {
	    value = value.replace("<br>", "");
	    $.ajax({
	        url: "index.php?r=package/setcost",
	        type: "post",
	        data: {
	            id: id,
	            value: value
	        },
	        success: function(response){
	            $('#actionMessage').html('<div class="alert alert-success">Стоимость заявки изменена</div>');
	        },
	        error:function(response) {
	            $('#actionMessage').html('<div class="alert alert-danger">Стоимость заявки не изменена</div>');
	        }
	    })
	}
JS;

$ajaxDeleteScript = <<< JS
    $(document).on('ready pjax:success', function() {
        $('.ajaxDelete').on('click', function(event) {
            event.preventDefault();
            var deleteUrl = $(this).attr('url');
            var pjaxContainer = $(this).attr('pjax-container');
            if (confirm('Вы действительно хотите удалить данную заявку?')) {
                $.ajax({
                    url: deleteUrl,
                    type: 'post',
                    error: function(xhr, status, error) {
                        $('#actionMessage').html('<div class="alert alert-danger">'+ xhr.responseText +'</div>');
                    }
                }).done(function(data){
                    $.pjax.reload({container: '#' + $.trim(pjaxContainer)});
                    $('#actionMessage').html('<div class="alert alert-success">Заявка успешно удалена</div>');
                });
            }
        })
    });
JS;


echo '<div id="actionMessage">';
echo ActionStatusMessage::widget([]);
echo '</div>';

$this->registerJs($setCostScript, \yii\web\View::POS_BEGIN);
$this->registerJs($ajaxDeleteScript);

echo $this->render($partial, ['packages' => $packages]);
