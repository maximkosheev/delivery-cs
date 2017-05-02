<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 31.03.2017
 * Time: 11:33
 */

namespace app\controllers;

use app\models\Deliveryman;
use app\models\FinancesFilter;
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
        $model = new ReceiptForm(['time' => date('Y-m-d H:i:s', time())]);
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
            $log->time = $model->time;
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
        $deliverymans = Deliveryman::find()->all();

        $x = [];
        foreach ($deliverymans as $deliveryman) {
            if ($deliveryman->balance !== 0) {
                $x[] = $deliveryman;
            }
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $x,
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

        $filterModel = new FinancesFilter();

        $packageQuery = Package::find()->where(['deliveryman_id' => $id]);
        $receiptQuery = FinancesLog::find()->where(['deliveryman_id' => $id]);

        if ($filterModel->load(\Yii::$app->request->post())) {
            if ($filterModel->dateFrom != null) {
                $dateFrom = \DateTime::createFromFormat('d-m-Y', $filterModel->dateFrom);
                $packageQuery->andWhere(['>=', 'create_time', $dateFrom->format('Y-m-d 00:00:00')]);
                $receiptQuery->andWhere(['>=', 'time', $dateFrom->format('Y-m-d 00:00:00')]);
            }

            if ($filterModel->dateTo != null) {
                $dateTo = \DateTime::createFromFormat('Y-m-d', $filterModel->dateTo)->add(new \DateInterval('P1D'));
                $packageQuery->andWhere(['<', 'create_time', $dateTo->format('Y-m-d 00:00:00')]);
                $receiptQuery->andWhere(['<', 'time', $dateTo->format('Y-m-d 00:00:00')]);
            }
        }

        $financesItems = [];
        // собираем информацию о финансовых операциях по заявкам
        $packages = $packageQuery
            ->orderBy('create_time DESC')
            ->all();
        foreach ($packages as $package) {
            switch ($package->status) {
                case Package::STATUS_APPLIED:
                    // информация о закупке товара
                    $financesItems[] = new FinancesItem([
                        'time' => $package->open_time,
                        'cash' => -$package->purchase_price,
                        'description' => "Закупка по заявке #{$package->id}"
                    ]);
                    break;
                case Package::STATUS_DELIVERED:
                    // информация о закупке товара
                    $financesItems[] = new FinancesItem([
                        'time' => $package->open_time,
                        'cash' => -$package->purchase_price,
                        'description' => "Закупка по заявке #{$package->id}"
                    ]);
                    // информация о продаже товара
                    $financesItems[] = new FinancesItem([
                        'time' => $package->close_time,
                        'cash' => $package->selling_price,
                        'description' => "Продажа по заявке #{$package->id}"
                    ]);
                    // информация о доставке
                    $financesItems[] = new FinancesItem([
                        'time' => $package->close_time,
                        'cash' => -$package->cost,
                        'description' => "Доставка по заявке #{$package->id}"
                    ]);
                    break;
                case Package::STATUS_BACKOFF:
                    // информация о закупке товара
                    $financesItems[] = new FinancesItem([
                        'time' => $package->open_time,
                        'cash' => -$package->purchase_price,
                        'description' => "Закупка по заявке #{$package->id}"
                    ]);
                    // информация о возврате товара
                    $financesItems[] = new FinancesItem([
                        'time' => $package->close_time,
                        'cash' => $package->purchase_price,
                        'description' => "Возврат по заявке #{$package->id}"
                    ]);
                    // информация о доставке
                    $financesItems[] = new FinancesItem([
                        'time' => $package->close_time,
                        'cash' => -$package->cost,
                        'description' => "Доставка по заявке #{$package->id}"
                    ]);
                    break;
            }
        }

        $a = $receiptQuery
            ->orderBy('time DESC')
            ->all();
        foreach ($a as $o) {
            $financesItems[] = new FinancesItem([
                'time' => $o->time,
                'cash' => $o->cash,
                'description' => $o->description
            ]);
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $financesItems,
            'sort' => [
                'defaultOrder' => 'time DESC'
            ],
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        return $this->render('deliveryman', [
            'deliveryman' => $deliveryman,
            'dataProvider' => $dataProvider,
            'filterModel' => $filterModel
        ]);
    }
}