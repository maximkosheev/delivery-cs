<?php
/**
 * Created by PhpStorm.
 * User: kme
 * Date: 07.09.2016
 * Time: 9:05
 */

namespace app\common\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\ServerErrorHttpException;

class UploadFileBehavior extends Behavior
{
    public $destDir;
    public $destName;
    public $destAttr;

    protected $file;

    protected $destLink;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'saveFile',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'saveFile'
        ];
    }

    protected function getDestinationDirectory()
    {
        $model = $this->owner;

        if (!isset($this->destDir))
            return '';

        if (is_string($this->destDir)) {
            if (method_exists($model, $this->destDir))
                return call_user_func([$model, $this->destDir]);
            else if (property_exists($model, $this->destDir))
                return $model->{$this->destDir};
            else
                return $this->destDir;
        }
        else {
            return call_user_func($this->destDir);
        }
    }

    protected function getDestinationFileName()
    {
        $model = $this->owner;

        $this->file =$model->file;

        // формируем имя файла на сервере
        // если имя файла не задано, то берем оригинальное имя файла
        if (!isset($this->destName)) {
            return $this->file->baseName;
        }

        // задана некая строка
        if (is_string($this->destName)) {
            // этом может быть имя метода
            if (method_exists($model, $this->destName))
                return call_user_func([$model, $this->destName]);
            // может быть именем свойства
            else if (property_exists($model, $this->destName))
                return $model->{$this->destName};
            // просто константное значение
            else
                return $this->destName;
        }
        // задано замыкание
        else {
            return call_user_func($this->destName);
        }
    }

    public function saveFile()
    {
        $model = $this->owner;
        $this->file =$model->file;

        $this->destDir = $this->getDestinationDirectory();
        $this->destName = $this->getDestinationFileName();

        if ($this->file !== null) {
            $this->destLink = $this->destDir . $this->destName . '.' . $this->file->extension;
            if (!$this->file->saveAs(\Yii::$app->basePath . $this->destLink))
                throw new ServerErrorHttpException('Не удалось сохранить фотографию');
            $model->{$this->destAttr} = $this->destLink;
        }
    }
}