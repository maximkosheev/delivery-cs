<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 17.04.2017
 * Time: 12:14
 */

namespace app\controllers;

use app\models\Stock;
use app\models\StockSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class StockController extends Controller
{
    public function actionIndex()
    {
        if (!\Yii::$app->user->can('manager'))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        $stock = new Stock();

        if ($stock->load(\Yii::$app->request->post()) && $stock->save()) {
            $stock = new Stock();
        }

        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'stock' => $stock
        ]);
    }

    public function actionDelete($id)
    {
        $model = Stock::findOne(['id' => $id]);

        if ($model === null)
            throw new NotFoundHtmlException('Товар не найден');

        if (!\Yii::$app->user->can('admin'))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        if (!$model->delete())
            throw new \Exception('Товар не удален');
    }
}