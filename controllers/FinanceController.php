<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 31.03.2017
 * Time: 11:33
 */

namespace app\controllers;

use app\models\Deliveryman;
use app\models\ReceiptForm;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class FinanceController
 * @package app\controllers
 * Действия, связанные с финансами
 */
class FinanceController extends Controller
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

            if ($deliveryman->updateBalance($model->cash))
                \Yii::$app->session->setFlash('actionSuccess', "На счет курьера {$deliveryman->fio} успешно отправлено {$model->cash} рублей");
            else
                \Yii::$app->session->setFlash('actionFailed', "Деньги на счет не поступили");
        }

        return $this->render('receipt', [
            'model' => $model,
            'deliverymans' => ArrayHelper::map($delivermans, 'user_id', 'fio')
        ]);
    }
}