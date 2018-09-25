<?php

namespace app\components;

use yii\base\Model;

/**
 * Class MongoEmbedModel
 * @package app\components
 *
 * @property MongoModel $__parent;
 */
class MongoEmbedModel extends Model
{
    protected $__parent;
    protected $__attribute;

    public function save(){
        $this->__parent->updateEmbed($this->__attribute);
    }

    public function setParent($parent){
        $this->__parent = $parent;
    }

    public function setAttribute($attribute){
        $this->__attribute = $attribute;
    }
}
