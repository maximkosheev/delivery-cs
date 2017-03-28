<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 28.12.2016
 * Time: 11:48
 */

namespace app\modules\api\controllers;

use app\models\Deliveryman;
use app\models\Package;
use app\models\User;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use app\common\filters\UserContextFilter;

class ApiController extends Controller
{
    private $_user;

    public function behaviors()
    {
        return [
            'userContext' => [
                'class' => UserContextFilter::className(),
                'filter' => function() {
                    if (!isset($_GET['login']) || !isset($_GET['pwd']))
                        $this->_sendResponse(401);

                    $this->_user = User::findByUsername($_GET['login']);
                    if ($this->_user !== null) {
                        if (!$this->_user->validatePassword($_GET['pwd']) || !$this->_user->active)
                            $this->_user = null;
                    }

                    if ($this->_user === null)
                        $this->_sendResponse(401);

                    return true;
                }
            ]
        ];
    }

    private function _sendResponse($status = 200, $body = '')
    {
        if (empty($body)) {
            $message = '';
            switch($status) {
                case 401:
                    $message = 'You must be authorized to view thid page.';
                    break;
                case 404:
                    $message = 'The requested URL '.$_SERVER['REQUEST_URI'].' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];
            $body = $this->render('template', [
                'status' => $status,
                'statusCodeMessage' => $this->_getStatusCodeMessage($status),
                'message' => $message,
                'signature' => $signature,
            ]);
        }
        http_response_code($status);
        echo $body;
        exit(1);
    }

    private function _getStatusCodeMessage($status)
    {
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    public function actionRegister($token)
    {
        if ($this->_user->registerToken($token))
            $this->_sendResponse(200, json_encode(array('status'=>'success', 'data'=>null)));
        else
            $this->_sendResponse(500);
    }

    public function actionUnregister()
    {
        if ($this->_user->unregisterToken())
            $this->_sendResponse(200, json_encode(array('status'=>'success', 'data'=>null)));
        else
            $this->_sendResponse(500);
    }

    /**
     * Возвращает список заявок доступных данному курьеру
     * Доступной заявка считается в случае, если она вакантна и курьер может взять посылку данного типа
     * или курьер уже назначен исполнителем данной заявки
     */
    public function actionRequests()
    {
        // массив заявок, доступных для данного курьера
        $availablePackages = Package::find()
            ->with(['packageType', 'manager'])
            ->innerJoin('tbl_user_package_type_assignment', 'tbl_package.package_type = tbl_user_package_type_assignment.type_id')
            ->where(['tbl_user_package_type_assignment.user_id' => $this->_user->id])
            ->andWhere(['tbl_package.status' => Package::STATUS_NOT_APPLIED])
            ->all();
        // массив заявок, исполнителем которых назначен данный курьер
        $myPackages = Package::find()
            ->with('packageType')
            ->where(['deliveryman_id' => $this->_user->id])
            ->andWhere(['<', 'status', Package::STATUS_DELIVERED])
            ->all();

        // общий список заявок
        $packages = ArrayHelper::merge($availablePackages, $myPackages);

        if (count($packages) === 0)
            $this->_sendResponse(200, json_encode(array('status'=>'success', 'data'=>null)));
        else {
            // превращаем массив моделей в массив полей
            $rows = [];
            foreach($packages as $package) {
                $rows[] = array(
                    'id'                =>$package->id,
                    'manager'           =>$package->manager != null ? $package->manager->fio : 'Администратор',
                    'address_from'      =>$package->address_from,
                    'address_dest'      =>$package->address_to,
                    'phone_from'        =>$package->phone_from,
                    'phone_dest'        =>$package->phone_to,
                    'model'             =>$package->model,
                    'package_type'      =>$package->packageType->type,
                    'purchase_price'    =>$package->purchase_price,
                    'selling_price'     =>$package->selling_price,
                    'delivery_type'     =>$package->delivery_type,
                    'more'              =>$package->more,
                    'cost'              =>$package->cost,
                    'status'            =>$package->status,
                    'create_time'       =>$package->create_time,
                    'deadline_time'     =>$package->deadline_time,
                );
            }
            $this->_sendResponse(200, json_encode(['status'=>'success', 'data'=>$rows]));
        }
    }

    /**
     * Возвращает список заявок, которых данный курьер уже доставил
     */
    public function actionOrders()
    {
        $packages = Package::find()
            ->with(['packageType', 'manager'])
            ->where(['deliveryman_id' => $this->_user->id])
            ->andWhere(['>=', 'status', Package::STATUS_DELIVERED])
            ->all();

        if (count($packages) === 0)
            $this->_sendResponse(200, json_encode(array('status'=>'success', 'data'=>null)));
        else {
            // превращаем массив моделей в массив полей
            $rows = [];
            foreach($packages as $package) {
                $rows[] = array(
                    'id'                =>$package->id,
                    'manager'           =>$package->manager != null ? $package->manager->fio : 'Администратор',
                    'address_from'      =>$package->address_from,
                    'address_dest'      =>$package->address_to,
                    'phone_from'        =>$package->phone_from,
                    'phone_dest'        =>$package->phone_to,
                    'model'             =>$package->model,
                    'package_type'      =>$package->packageType->type,
                    'purchase_price'    =>$package->purchase_price,
                    'selling_price'     =>$package->selling_price,
                    'delivery_type'     =>$package->delivery_type,
                    'more'              =>$package->more,
                    'cost'              =>$package->cost,
                    'status'            =>$package->status,
                    'create_time'       =>$package->create_time,
                    'close_time'        =>$package->close_time,
                    'deadline_time'     =>$package->deadline_time,
                );
            }
            $this->_sendResponse(200, json_encode(['status'=>'success', 'data'=>$rows]));
        }
    }

    public function actionView($id)
    {
        $package = Package::find()
            ->with(['packageType', 'manager'])
            ->where(['id' => $id])
            ->one();

        if ($package === null)
            $this->_sendResponse(404, sprintf('Package not found with id %d', $id));
        else {
            $data = array(
                'id'=>$package>id,
                'manager'           =>$package->manager != null ? $package->manager->fio : 'Администратор',
                'address_from'      =>$package->address_from,
                'address_dest'      =>$package->address_to,
                'phone_from'        =>$package->phone_from,
                'phone_dest'        =>$package->phone_to,
                'model'             =>$package->model,
                'package_type'      =>$package->packageType->type,
                'purchase_price'    =>$package->purchase_price,
                'selling_price'     =>$package->selling_price,
                'delivery_type'     =>$package->delivery_type,
                'more'              =>$package->more,
                'cost'              =>$package->cost,
                'status'            =>$package->status,
                'create_time'       =>$package->create_time,
                'open_time'         =>$package->open_time,
                'close_time'        =>$package->close_time,
                'deadline_time'     =>$package->deadline_time,
            );
            $this->_sendResponse(200, json_encode(['status'=>'success', 'data'=>$data]));
        }
    }

    public function actionOpen($id)
    {
        $package = Package::find()
            ->where(['id' => $id])
            ->one();

        if ($package === null)
            $this->_sendResponse(404, sprintf('Package not found with id %d', $id));

        $deliveryman = Deliveryman::findOne(['user_id' => $this->_user->id]);

        if ($deliveryman === null)
            $this->_sendResponse(401, 'You must authorized as deliveryman');

        if ($deliveryman->undertake($package))
            $this->_sendResponse(200, json_encode(['status'=>'success']));
        else
            $this->_sendResponse(200, json_encode(['status'=>'failed', 'msg'=>'Order is already in use']));
    }

    public function actionGet($id)
    {
        $package = Package::find()
            ->where(['id' => $id])
            ->one();

        if ($package === null)
            $this->_sendResponse(404, sprintf('Package not found with id %d', $id));

        $deliveryman = Deliveryman::findOne(['user_id' => $this->_user->id]);

        if ($deliveryman === null)
            $this->_sendResponse(401, 'You must authorized as deliveryman');

        if ($deliveryman->collectit($package))
            $this->_sendResponse(200, json_encode(['status'=>'success']));
        else
            $this->_sendResponse(500);
    }

    public function actionClose($id)
    {
        $package = Package::find()
            ->where(['id' => $id])
            ->one();

        if ($package === null)
            $this->_sendResponse(404, sprintf('Package not found with id %d', $id));

        $deliveryman = Deliveryman::findOne(['user_id' => $this->_user->id]);

        if ($deliveryman === null)
            $this->_sendResponse(401, 'You must authorized as deliveryman');

        if ($deliveryman->delivered($package))
            $this->_sendResponse(200, json_encode(['status'=>'success']));
        else
            $this->_sendResponse(500);
    }

    public function actionCancel($id)
    {
        $package = Package::find()
            ->where(['id' => $id])
            ->one();

        if ($package === null)
            $this->_sendResponse(404, sprintf('Package not found with id %d', $id));

        $deliveryman = Deliveryman::findOne(['user_id' => $this->_user->id]);

        if ($deliveryman === null)
            $this->_sendResponse(401, 'You must authorized as deliveryman');

        if ($deliveryman->canceled($package))
            $this->_sendResponse(200, json_encode(['status'=>'success']));
        else
            $this->_sendResponse(500);
    }

    public function actionBackoff($id)
    {
        $package = Package::find()
            ->where(['id' => $id])
            ->one();

        if ($package === null)
            $this->_sendResponse(404, sprintf('Package not found with id %d', $id));

        $deliveryman = Deliveryman::findOne(['user_id' => $this->_user->id]);

        if ($deliveryman === null)
            $this->_sendResponse(401, 'You must authorized as deliveryman');

        if ($deliveryman->backoff($package))
            $this->_sendResponse(200, json_encode(['status'=>'success']));
        else
            $this->_sendResponse(500);
    }
}