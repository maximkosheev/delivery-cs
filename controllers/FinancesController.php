<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 31.03.2017
 * Time: 11:33
 */

namespace app\controllers;

use app\models\Deliveryman;
use app\models\FinancesItem;
use app\models\Package;
use app\models\ReceiptForm;
use app\models\FinancesLog;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class FinancesController
 * @package app\controllers
 * Действия, связанные с финансами
 */
class FinancesController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin']
                    ]
                ]
            ]
        ];
    }

    /* Поступление денежных средств на счет курьера */
    public function actionReceipt()
    {
        $model = new ReceiptForm();
        $delivermans = Deliveryman::find()
            ->innerJoin('tbl_user', 'tbl_deliveryman.user_id = tbl_user.id')
            ->where(['active' => 1])
            ->all();
        if ($model->load(\Yii::$app->request->post())) {
            $deliveryman = Deliveryman::findOne(['user_id' => $model->deliveryman_id]);

            if ($deliveryman === null)
                throw new NotFoundHttpException('Выбранный курьер не найден');

            $log = new FinancesLog();
            $log->deliveryman_id = $deliveryman->user_id;
            $log->cash = $model->cash;
            $log->description = $model->description;
            if ($log->save(false))
                \Yii::$app->session->setFlash('actionSuccess', "На счет курьера {$deliveryman->fio} успешно отправлено {$model->cash} рублей");
            else
                \Yii::$app->session->setFlash('actionFailed', "Деньги на счет не поступили");
        }

        return $this->render('receipt', [
            'model' => $model,
            'deliverymans' => ArrayHelper::map($delivermans, 'user_id', 'fio')
        ]);
    }

    public function actionDeliverymans()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Deliveryman::find(),
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        return $this->render('deliverymans', [
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionDeliveryman($id)
    {
        $deliveryman = Deliveryman::findOne(['user_id' => $id]);

        if ($deliveryman === null)
            throw new NotFoundHttpException('Курьер не найден');

        $financesItems = [];
        // собираем информацию о финансовых операциях по заявкам
        $packages = Package::findAll(['deliveryman_id' => $id]);
        foreach ($packages as $package) {
            switch ($package->status) {
                case Package::STATUS_APPLIED:
                    // информация о закупке товара
                    $financesItem = new FinancesItem();
                    $financesItem->time = $package->open_time;
                    $financesItem->cash = -$package->purchase_price;
                    $financesItem->description = "Закупка по заявке #{$package->id}";
                    $financesItems[] = $financesItem;
                    break;
                case Package::STATUS_DELIVERED:
                    // информация о закупке товара
                    $financesItem = new FinancesItem();
                    $financesItem->time = $package->open_time;
                    $financesItem->cash = -$package->purchase_price;
                    $financesItem->description = "Закупка по заявке #{$package->id}";
                    $financesItems[] = $financesItem;
                    // информация о продаже товара
                    $financesItem = new FinancesItem();
                    $financesItem->time = $package->close_time;
                    $financesItem->cash = $package->selling_price;
                    $financesItem->description = "Продажа по заявке #{$package->id}";
                    $financesItems[] = $financesItem;
                    // информация о доставке
                    $financesItem = new FinancesItem();
                    $financesItem->time = $package->close_time;
                    $financesItem->cash = -$package->cost;
                    $financesItem->description = "Доставка по заявке #{$package->id}";
                    $financesItems[] = $financesItem;
                    break;
                case Package::STATUS_BACKOFF:
                    // информация о закупке товара
                    $financesItem = new FinancesItem();
                    $financesItem->time = $package->open_time;
                    $financesItem->cash = -$package->purchase_price;
                    $financesItem->description = "Закупка по заявке #{$package->id}";
                    $financesItems[] = $financesItem;
                    // информация о возврате товара
                    $financesItem = new FinancesItem();
                    $financesItem->time = $package->close_time;
                    $financesItem->cash = $package->purchase_price;
                    $financesItem->description = "Возврат по заявке #{$package->id}";
                    $financesItems[] = $financesItem;
                    // информация о доставке
                    $financesItem = new FinancesItem();
                    $financesItem->time = $package->close_time;
                    $financesItem->cash = -$package->cost;
                    $financesItem->description = "Доставка по заявке #{$package->id}";
                    $financesItems[] = $financesItem;
                    break;
            }
            $financesItems[] = $financesItem;
        }

        $a = FinancesLog::findAll(['deliveryman_id' => $id]);
        foreach ($a as $o) {
            $financesItem = new FinancesItem();
            $financesItem->time = $o->time;
            $financesItem->cash = $o->cash;
            $financesItem->description = $o->description;
            $financesItems[] = $financesItem;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $financesItems,
            'sort' => [
                'defaultOrder' => 'time asc'
            ],
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        return $this->render('deliveryman', [
            'deliveryman' => $deliveryman,
            'dataProvider' => $dataProvider
        ]);
    }
}