<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 12.12.2016
 * Time: 12:29
 */

namespace app\controllers;

use app\models\Deliveryman;
use app\models\HistoryForm;
use app\models\Manager;
use app\models\Package;
use app\models\PackageType;
use app\models\User;
use app\components\GCMService;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class PackageController extends Controller
{
    public function behaviors()
    {
        return [
       ];
    }

    public function actionIndex($status = null)
    {
        $query = Package::find()->
            with(['manager', 'deliveryman'])
            ->orderBy('create_time DESC');

        // курьеру доступны только вакантные доставки или доставки связанные с этим курьером
        if (\Yii::$app->user->identity->isDeliveryman) {
            // отображаем только заявки, находящиеся в работе, связанные с данным курьером
            if ($status === 'current') {
                $query->where(['deliveryman_id' => \Yii::$app->user->id])
                    ->andWhere(['>', 'status', Package::STATUS_NOT_APPLIED])
                    ->andWhere(['<', 'status', Package::STATUS_DELIVERED]);
            }
            // отображаем только вакантные для данного курьера заявки
            else if ($status == 0) {
                $query
                    ->innerJoin('tbl_user_package_type_assignment', 'package_type = type_id')
                    ->where([
                        'status' => Package::STATUS_NOT_APPLIED,
                        'user_id' => \Yii::$app->user->id
                    ]);
            }
            else {
                throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');
            }
        }
        // менеджеру доступные все заявки
        else {
            if ($status !== null) {
                if ($status == Package::STATUS_OPEN) {
                    $query
                        ->andWhere(['>=', 'status', Package::STATUS_APPLIED])
                        ->andWhere(['<=', 'status', Package::STATUS_DELIVERED]);
                }
                else {
                    $query->where(['status' => $status]);
                }
            }
        }

        $packages = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        return $this->render('index', [
            'packages' => $packages
        ]);
    }

    public function actionCreate()
    {
        if (!\Yii::$app->user->can('createDelivery'))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        $package = new Package();

        if ($package->load(\Yii::$app->request->post())) {
            if ($package->save()) {
                \Yii::$app->session->setFlash('actionSuccess', 'Заявка успешно создана!');
                // находим всех курьеров, которые могут выполнить данную заявку
                $deliverymans = User::find()
                    ->leftJoin('tbl_user_package_type_assignment', 'tbl_user.id = tbl_user_package_type_assignment.user_id')
                    ->where(['tbl_user_package_type_assignment.type_id' => $package->package_type])
                    ->all();
                $registration_ids = [];
                $subject = 'Новая заявка';
                $message = [
                    'id' => $package->id,
                    'address_from' => $package->address_from,
                    'address_to' => $package->address_to,
                ];
                foreach ($deliverymans as $deliveryman) {
                    $registration_ids[] = $deliveryman->token;
                }
                // отправляем запрос на доставку уведомлений
                GCMService::sendNotification($registration_ids, $subject, $message);
                $this->redirect(['package/index']);
            }
            else
                \Yii::$app->session->setFlash('actionFailed', 'Что-то пошло не так и заявка не создана');
        }

        $lastPackages = new ActiveDataProvider([
            'query' => Package::find()
                ->where(['<', 'status', Package::STATUS_DELIVERED])
                ->orderBy('create_time ASC'),
            'pagination' => [
                'pageSize' => -1
            ]
        ]);

        return $this->render('create', [
            'package' => $package,
            'types' => ArrayHelper::map(PackageType::find()->all(), 'id', 'type'),
            'models' => $this->models,
            'lastPackages' => $lastPackages
        ]);
    }

    /**
     * Возвращает курьеров, для которых задан данный тип доставки
     * @param $packageType - указанный тип заправки
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionDeliverymans($packageType)
    {
        if (!\Yii::$app->request->isAjax)
            throw new BadRequestHttpException('Only ajax request allowed');

        $deliverymans = Deliveryman::find()
            ->innerJoin('tbl_user', 'tbl_user.id = tbl_deliveryman.user_id')
            ->innerJoin('tbl_user_package_type_assignment', 'tbl_user_package_type_assignment.user_id = tbl_deliveryman.user_id')
            ->where(['type_id' => $packageType])
            ->andWhere(['active' => 1])
            ->all();

        \Yii::$app->response->format = Response::FORMAT_JSON;
        return json_encode(ArrayHelper::map($deliverymans, 'user_id', 'fio'));
    }

    protected function getModels()
    {
        return require(__DIR__.'/../data/models.php');
    }

    public function actionUpdate($id)
    {
        $package = Package::find()
            ->with(['manager', 'deliveryman'])
            ->where(['id'=>$id])
            ->one();

        if ($package === null)
            throw new NotFoundHttpException('Посылка не найдена');

        if (!\Yii::$app->user->can('updateDelivery', ['package' => $package]))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        $oldStatus = $package->status;
        if ($package->load(\Yii::$app->request->post())) {
            // если задан исполнитель, открываем заявку
            if ($package->isAttributeChanged('deliveryman_id', false) && $package->deliveryman_id != null) {
                $package->status = Package::STATUS_APPLIED;
                $package->open_time = date('Y-m-d H:i:s', time());
            }
            if (empty($package->status))
                $package->status = $oldStatus;
            if ($package->save()) {
                \Yii::$app->session->setFlash('actionSuccess', 'Заявка успешно сохранена!', false);
                $this->redirect(['package/index']);
            }
            else
                \Yii::$app->session->setFlash('actionFailed', 'Что-то пошло не так и заявка не сохранилась', false);
        }

        return $this->render('update', [
            'package' => $package,
            'types' => ArrayHelper::map(PackageType::find()->all(), 'id', 'type'),
            'models' => $this->models,
        ]);
    }

    public function actionView($id)
    {
        $package = Package::find()
            ->with(['manager', 'deliveryman'])
            ->where(['id'=>$id])
            ->one();

        if ($package === null)
            throw new NotFoundHttpException('Посылка не найдена');

        return $this->render('view', [
            'package' => $package
        ]);
    }

    public function actionDelete($id)
    {
        if (!\Yii::$app->user->can('deleteDelivery'))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        $package = Package::findOne(['id' => $id]);

        if ($package === null)
            throw new NotFoundHttpException('Посылка не найдена');

        if (!$package->delete())
            throw new \Exception('Посылка не удалена');
    }

    public function actionUndertake($id)
    {
        $package = Package::find()
            ->with(['manager', 'deliveryman'])
            ->where(['id'=>$id])
            ->one();

        if ($package === null)
            throw new NotFoundHttpException('Посылка не найдена');

        $user = Deliveryman::find()
            ->with('packageTypes')
            ->where(['user_id' => \Yii::$app->user->id])
            ->one();

        if ($user === null)
            throw new NotFoundHttpException('Пользователь не найден');

        if (!$user->undertake($package))
            throw new \HttpRequestException('Заявка или уже занята, или для Вас не досупны заявки данного типа');

        \Yii::$app->session->setFlash('actionSuccess', 'Вы назначены исполнителем по данной заявке', false);
        $this->redirect(['view', 'id'=>$id]);
    }

    public function actionCollectit($id)
    {
        $package = Package::find()
            ->with(['manager', 'deliveryman'])
            ->where(['id'=>$id])
            ->one();

        if ($package === null)
            throw new NotFoundHttpException('Посылка не найдена');

        $user = Deliveryman::find()
            ->with('packageTypes')
            ->where(['user_id' => \Yii::$app->user->id])
            ->one();

        if ($user === null)
            throw new NotFoundHttpException('Пользователь не найден');

        if (!$user->collectit($package))
            throw new \HttpRequestException('Заявка или уже занята, или для Вас не досупны заявки данного типа');

        \Yii::$app->session->setFlash('actionSuccess', 'Статус заявки изменен', false);
        $this->redirect(['view', 'id'=>$id]);
    }

    public function actionCanceled($id)
    {
        $package = Package::find()
            ->with(['manager', 'deliveryman'])
            ->where(['id'=>$id])
            ->one();

        if ($package === null)
            throw new NotFoundHttpException('Посылка не найдена');

        $user = Deliveryman::find()
            ->with('packageTypes')
            ->where(['user_id' => \Yii::$app->user->id])
            ->one();

        if ($user === null)
            throw new NotFoundHttpException('Пользователь не найден');

        if (!$user->canceled($package))
            throw new \HttpRequestException('Заявка или уже занята, или для Вас не досупны заявки данного типа');

        \Yii::$app->session->setFlash('actionSuccess', 'Статус заявки изменен', false);
        $this->redirect(['view', 'id'=>$id]);
    }

    public function actionDelivered($id)
    {
        $package = Package::find()
            ->with(['manager', 'deliveryman'])
            ->where(['id'=>$id])
            ->one();

        if ($package === null)
            throw new NotFoundHttpException('Посылка не найдена');

        $user = Deliveryman::find()
            ->with('packageTypes')
            ->where(['user_id' => \Yii::$app->user->id])
            ->one();

        if ($user === null)
            throw new NotFoundHttpException('Пользователь не найден');

        if (!$user->delivered($package))
            throw new \HttpRequestException('Заявка или уже занята, или для Вас не досупны заявки данного типа');

        \Yii::$app->session->setFlash('actionSuccess', 'Статус заявки изменен', false);
        $this->redirect(['view', 'id'=>$id]);
    }

    public function actionBackoff($id)
    {
        $package = Package::find()
            ->with(['manager', 'deliveryman'])
            ->where(['id'=>$id])
            ->one();

        if ($package === null)
            throw new NotFoundHttpException('Посылка не найдена');

        $user = Deliveryman::find()
            ->with('packageTypes')
            ->where(['user_id' => \Yii::$app->user->id])
            ->one();

        if ($user === null)
            throw new NotFoundHttpException('Пользователь не найден');

        if (!$user->backoff($package))
            throw new \HttpRequestException('Заявка или уже занята, или для Вас не досупны заявки данного типа');

        \Yii::$app->session->setFlash('actionSuccess', 'Статус заявки изменен', false);
        $this->redirect(['view', 'id'=>$id]);
    }

    public function actionHistory()
    {
        $model = new HistoryForm();

        $packages = Package::find()
            ->with(['manager', 'deliveryman'])
            ->orderBy('create_time DESC');
        $deliverymans = Deliveryman::find();
        $managers = Manager::find();

        if ($model->load(\Yii::$app->request->get())) {
            if (!empty($model->dateFrom)) {
                $dateFrom = \DateTime::createFromFormat('d-m-Y', $model->dateFrom);
                $packages->andWhere(['>=', 'create_time', $dateFrom->format('Y-m-d')]);
            }
            if (!empty($model->dateTo)) {
                $dateTo = \DateTime::createFromFormat('d-m-Y', $model->dateTo);
                $dateTo->add(new \DateInterval('P1D'));
                $packages->andWhere(['<', 'create_time', $dateTo->format('Y-m-d')]);
            }
            if (!empty($model->manager_id))
                $packages->andWhere(['=', 'manager_id', $model->manager_id]);
            if (!empty($model->deliveryman_id))
                $packages->andWhere(['=', 'deliveryman_id', $model->deliveryman_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $packages,
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        return $this->render('history', [
            'model' => $model,
            'deliverymans' => ArrayHelper::map($deliverymans->all(), 'user_id', 'fio'),
            'managers' => ArrayHelper::map($managers->all(), 'user_id', 'fio'),
            'packages' => $dataProvider
        ]);
    }

    public function actionHistory2()
    {
        $model = new HistoryForm();

        $packages = Package::find()
            ->with(['manager', 'deliveryman'])
            ->orderBy('create_time DESC');

        if ($model->load(\Yii::$app->request->get())) {
            if (!empty($model->dateFrom)) {
                $dateFrom = \DateTime::createFromFormat('d-m-Y', $model->dateFrom);
                $packages->andWhere(['>=', 'create_time', $dateFrom->format('Y-m-d')]);
            }
            if (!empty($model->dateTo)) {
                $dateTo = \DateTime::createFromFormat('d-m-Y', $model->dateTo);
                $dateTo->add(new \DateInterval('P1D'));
                $packages->andWhere(['<', 'create_time', $dateTo->format('Y-m-d')]);
            }
            if (!empty($model->status)) {
                if ($model->status === Package::STATUS_OPEN) {
                    $packages->andWhere(['>', 'status', Package::STATUS_NOT_APPLIED])->andWhere(['<', 'status', Package::STATUS_DELIVERED]);
                }
                else
                    $packages->andWhere(['=', 'status', $model->status]);
            }
        }

        $packages->andWhere(['=', 'deliveryman_id', \Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $packages,
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        return $this->render('history2', [
            'model' => $model,
            'packages' => $dataProvider
        ]);
    }

    public function actionSetcost()
    {
        if (isset($_POST['id']) && isset($_POST['value'])) {
            $package = Package::findOne(['id' => $_POST['id']]);
            if ($package === null)
                throw new NotFoundHttpException('Посылка не найдена');
            if (!is_numeric($_POST['value']))
                throw new InvalidParamException('Параметр должен быть числом');
            $package->cost = $_POST['value'];
            if (!$package->save(false, ['cost']))
                throw new ServerErrorHttpException('Update cost failed');
        }
        else
            throw new InvalidParamException('Wrong request params exception');
    }
}