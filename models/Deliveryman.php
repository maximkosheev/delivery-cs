<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 08.12.2016
 * Time: 10:52
 */

namespace app\models;

use yii\base\Security;
use yii\helpers\Url;

/**
 * Class Deliveryman
 * @package app\models
 * @property float $balance Текущий баланс курьера
 */
class Deliveryman extends Worker
{
    public function behaviors()
    {
        return [
            'assignRole' => [
                'class' => 'app\common\behaviors\AssignRoleBehavior',
                'role' => 'deliveryman'
            ],
            'upload' => [
                'class' => 'app\common\behaviors\UploadFileBehavior',
                'destDir' => \Yii::getAlias('@uploads/photos/'),
                'destName' => function () {
                    return (new Security())->generateRandomString(8);
                },
                'destAttr' => 'photo'
            ]
        ];
    }

    public function getPackageTypes()
    {
        return $this->hasMany(PackageType::className(), ['id' => 'type_id'])
            ->viaTable('tbl_user_package_type_assignment', ['user_id' => 'user_id']);
    }

    public function getStatistic()
    {
        $statistic = new DeliverymanStatistic();
        $statistic->totalCount = Package::find()
            ->where([
                'deliveryman_id' => $this->user_id,
                'status' => Package::STATUS_DELIVERED
            ])
            ->count();
        $monthStart = date('Y-m-01 00:00:00', time());
        $monthEnd = date('Y-m-31 23:59:59', time());
        $statistic->mounthCount = Package::find()
            ->where([
                'deliveryman_id' => $this->user_id,
                'status' => Package::STATUS_DELIVERED,
            ])
            ->andWhere('close_time BETWEEN :fromDate AND :toDate', [':fromDate' => $monthStart, ':toDate' => $monthEnd])
            ->count();
        $statistic->successfulCount = Package::find()
            ->where([
                'deliveryman_id' => $this->user_id,
                'status' => Package::STATUS_DELIVERED,
            ])
            ->andWhere(['<', 'close_time', 'deadline_time'])
            ->count();
        $statistic->lateCount = Package::find()
            ->where([
                'deliveryman_id' => $this->user_id,
                'status' => Package::STATUS_DELIVERED,
            ])
            ->andWhere('close_time > deadline_time')
            ->count();
        return $statistic;
    }

    public static function getIndexUrl()
    {
        return Url::to(['user/deliverymans']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $packageTypes = \Yii::$app->request->post('packageTypes');
        // если требуется обновление списка типов посылок
        if ($packageTypes !== null) {
            /* обновление списка доступных типов посылок */
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                $db->createCommand()
                    ->delete('tbl_user_package_type_assignment', 'user_id=:user_id', [':user_id' => $this->user_id])
                    ->execute();
                // заново формируем список доступных типов посылок
                $rows = [];
                foreach ($packageTypes as $packageType) {
                    $rows[] = [$this->user_id, $packageType];
                }
                $db->createCommand()
                    ->batchInsert('tbl_user_package_type_assignment', ['user_id', 'type_id'], $rows)
                    ->execute();
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Проверяет может ли курьер взять заявку данного типа
     * @param \app\models\Package $package
     */
    public function canUndertake($package)
    {
        if (!$package->isVacant)
            return false;

        foreach ($this->packageTypes as $packageType) {
            if ($package->package_type == $packageType->id)
                return true;
        }
        return false;
    }

    /**
     * Устанавливает пользователя исполнителем по данной заявке
     * @param \app\models\Package $package
     */
    public function undertake($package)
    {
        if (!$this->canUndertake($package))
            return false;

        $package->deliveryman_id = $this->user_id;
        $package->status = Package::STATUS_APPLIED;
        $package->open_time = date('Y-m-d H:i:s', time());
        return $package->save(true, ['deliveryman_id', 'status', 'open_time']);

    }

    /**
     * Проверяем может ли данный пользователь забрать посылку
     * @param \app\models\Package $package
     */
    public function  canCollect($package)
    {
        if ($package->deliveryman_id == $this->user_id &&
            $package->status == Package::STATUS_APPLIED)
            return true;
        return false;
    }

    /**
     * Пользователь забрал посылку
     * @param \app\models\Package $package
     * @return bool true|false результат изменения статуса заявки
     */
    public function collectit($package)
    {
        if (!$this->canCollect($package))
            return false;
        $package->status = Package::STATUS_PICKUP;
        return $package->save(true, ['status']);
    }

    /**
     * Проверяем может ли данный пользовтель получить отказ от посылки со стороны получателя
     * @param \app\models\Package $package
     * @return bool
     */
    public function canCancel($package)
    {
        if ($package->deliveryman_id == $this->user_id &&
            $package->status > Package::STATUS_NOT_APPLIED &&
            $package->status < Package::STATUS_DELIVERED) {
            return true;
        }
        return false;
    }

    /**
     * Получатель отказался от посылки
     * @param \app\models\Package $package
     * @return bool
     */
    public function canceled($package)
    {
        if (!$this->canCancel($package))
            return false;
        $package->status = Package::STATUS_CANCELED;
        return $package->save(true, ['status']);
    }

    /**
     * Проверяет может ли данный дользователь завершить доставку
     * @param \app\models\Package $package
     * @return bool
     */
    public function canDelivered($package)
    {
        if (($package->status == Package::STATUS_PICKUP ||
            $package->status == Package::STATUS_APPLIED) &&
            $package->deliveryman_id == $this->user_id)
            return true;

        return false;
    }

    /**
     * Завершает доставку
     * @param \app\models\Package $package
     * @return bool
     */
    public function delivered($package)
    {
        if (!$this->canDelivered($package))
            return false;
        $package->status = Package::STATUS_DELIVERED;
        $package->close_time = date('Y-m-d H:i:s', time());
        return $package->save(true, ['status', 'close_time']);
    }

    /**
     * @param \app\models\Package $package
     * @return bool
     */
    public function canBackoff($package)
    {
        if ($package->deliveryman_id == $this->user_id &&
            $package->status > Package::STATUS_NOT_APPLIED && $package->status < Package::STATUS_DELIVERED)
            return true;
    }

    /**
     * @param \app\models\Package $package
     * @return bool
     */
    public function backoff($package)
    {
        if (!$this->canBackoff($package))
            return false;
        $package->status = Package::STATUS_BACKOFF;
        return $package->save(true);
    }

    /**
     * Изменяет значение баланса курьера на заданную величину
     * @param $value величина денежных средств, поступающих/списывающихся на/с счет(а) курьера
     */
    public function updateBalance($value)
    {
        $this->balance += $value;
        return $this->save(false, ['balance']);
    }
}