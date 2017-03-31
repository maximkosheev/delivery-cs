<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 07.12.2016
 * Time: 17:16
 */

namespace app\controllers;

use app\models\Deliveryman;
use app\models\Manager;
use app\models\PackageType;
use app\models\User;
use app\models\Worker;
use app\models\ChangePasswordForm;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ]
        ];
    }

    public function actionManagers()
    {
        $managers = new ActiveDataProvider([
            'query' => Manager::find()
                ->innerJoin('tbl_user', 'tbl_user.id = tbl_manager.user_id')
                ->with('identity')
                ->where(['tbl_user.active' => 1]),
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        return $this->render('managers', ['managers' => $managers]);
    }

    public function actionDeliverymans()
    {
        $deliverymans = new ActiveDataProvider([
            'query' => Deliveryman::find()
                ->with('identity'),
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        return $this->render('deliverymans', ['deliverymans' => $deliverymans]);
    }

    public function actionView($id)
    {
        $employer = Worker::findit($id);

        if ($employer === null)
            throw new NotFoundHttpException('Пользователь не найден');

        return $this->render('viewEmployer', [
            'employer' => $employer
        ]);
    }

    public function actionArchive()
    {
    }

    public function actionCreate($type)
    {
        if (strcmp($type, 'manager') === 0)
            return $this->createManager();
        else if (strcmp($type, 'deliveryman') === 0)
            return $this->createDeliveryman();

        throw new BadRequestHttpException('Unknown user type');
    }

    public function createManager()
    {
        if (!\Yii::$app->user->can('createManager'))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        $employer = new Manager(['scenario'=>'insert']);

        if ($employer->load(\Yii::$app->request->post()))
        {
            try {
                $employer->file = UploadedFile::getInstance($employer, 'file');
                if ($employer->save()) {
                    \Yii::$app->session->setFlash('actionSuccess', 'Менеджер успешно создан', false);
                    $this->redirect($employer->indexUrl);
                }
                else
                    throw new \Exception();
            }
            catch (\Exception $e) {
                \Yii::$app->session->setFlash('actionFailed', 'Произошла ошибка: '.$e->getMessage());
            }
        }

        return $this->render('createEmployer', ['model' => $employer]);
    }

    public function createDeliveryman()
    {
        if (!\Yii::$app->user->can('createDeliveryman'))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        $employer = new Deliveryman(['scenario'=>'insert']);

        if ($employer->load(\Yii::$app->request->post()))
        {
            try {
                $employer->file = UploadedFile::getInstance($employer, 'file');
                if ($employer->save()) {
                    \Yii::$app->session->setFlash('actionSuccess', 'Курьер успешно создан', false);
                    $this->redirect($employer->indexUrl);
                }
                else
                    throw new \Exception();
            }
            catch(\Exception $e) {
                \Yii::$app->session->setFlash('actionFailed', 'Произошла ошибка : '.$e->getMessage());
            }
        }

        return $this->render('createEmployer', [
            'model' => $employer,
            'allPackageTypes' => ArrayHelper::map(PackageType::find()->all(), 'id', 'type')
        ]);
    }

    public function actionUpdate($id)
    {
        $employer = Worker::findit($id);

        if ($employer === null)
            throw new NotFoundHttpException('Пользователь не найден');

        if (!\Yii::$app->user->can('updateProfile', ['user_id' => $employer->identity->id]))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        if ($employer->load(\Yii::$app->request->post())) {
            try {
                $employer->file = UploadedFile::getInstance($employer, 'file');
                if ($employer->save()) {
                    \Yii::$app->session->setFlash('actionSuccess', 'Данные пользователя сохранены', false);
                    $this->redirect($employer->indexUrl);
                }
                else
                    throw new \Exception();
            }
            catch(\Exception $e) {
                \Yii::$app->session->setFlash('actionFailed', 'Произошла ошибка');
            }
        }

        if ($employer instanceof Manager)
            return $this->render('updateEmployer', ['model'=>$employer]);
        else
            return $this->render('updateEmployer', [
                'model'=>$employer,
                'allPackageTypes' => ArrayHelper::map(PackageType::find()->all(), 'id', 'type')
            ]);
    }

    public function actionBlock($id)
    {
        $employer = Worker::findit($id);

        if ($employer === null)
            throw new NotFoundHttpException('Пользователь не найден');

        if (!\Yii::$app->user->can('removeProfile', ['user_id' => $employer->identity->id]))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        if ($employer->identity->block()) {
            \Yii::$app->session->setFlash('actionSuccess', 'Пользователь успешно заблокирован', false);
        }
        $this->redirect($employer->indexUrl);
    }

    public function actionUnblock($id)
    {
        $employer = Worker::findit($id);

        if ($employer === null)
            throw new NotFoundHttpException('Пользователь не найден');

        if (!\Yii::$app->user->can('updateProfile', ['user_id' => $employer->identity->id]))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        if ($employer->identity->unblock()) {
            \Yii::$app->session->setFlash('actionSuccess', 'Пользователь успешно разблокирован', false);
        }
        $this->redirect($employer->indexUrl);
    }

    public function actionChangepassword($id)
    {
        $model = new ChangePasswordForm();

        $user = User::findOne(['id' => $id]);

        if ($user === null)
            throw new NotFoundHttpException();

        if (!\Yii::$app->user->can('changePassword'))
            throw new ForbiddenHttpException('У вас не достаточно прав на выполнения данной операции');

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $user->password = md5($model->password);
            if ($user->save()) {
                \Yii::$app->session->setFlash('actionSuccess', 'Пароль успешно изменен', false);
                $this->redirect(['/user/update', 'id' => $user->id]);
            }
        }

        return $this->render('changePassword', [
            'model' => $model,
            'user' => $user
        ]);
    }
}