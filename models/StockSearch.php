<?php
/**
 * Created by PhpStorm.
 * User: MadMax
 * Date: 18.04.2017
 * Time: 15:38
 */

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class StockSearch extends Model
{
    public $id;
    public $brand;
    public $description;
    public $purchase_price;
    public $owner;

    public function rules()
    {
        return [
            [['id', 'brand', 'description', 'purchase_price', 'owner'], 'safe']
        ];
    }

    public function search($params)
    {
        $query = Stock::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'brand', $this->brand]);
        $query->andFilterWhere(['like', 'owner', $this->owner]);

        return $dataProvider;
    }
}