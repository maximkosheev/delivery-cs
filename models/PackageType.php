<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 13.12.2016
 * Time: 11:44
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class PackageType
 * @package app\models
 * @property integer $id
 * @property string $type
 * @property string $description
 */
class PackageType extends ActiveRecord
{
    public static function tableName()
    {
        return 'tbl_package_type';
    }

    public function rules()
    {
        return [
            ['type', 'required', 'message'=>'Поле не может быть пустым'],
            ['description', 'safe']
        ];
    }
}