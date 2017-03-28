<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 20.01.2017
 * Time: 11:16
 */

namespace app\common\filters;

use yii\base\ActionFilter;

class UserContextFilter extends ActionFilter
{
    public $filter;

    public function beforeAction($action)
    {
        if ($this->filter != null)
            return call_user_func($this->filter);
    }
}