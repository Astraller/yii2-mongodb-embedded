<?php

namespace app\components;

use \yii\mongodb\ActiveRecord;

class MongoModel extends ActiveRecord
{
    /** @var MongoEmbedModel[][]|MongoEmbedModel[]|[] $__embedItems */
    protected $__embedItems = [];

    public function embedAttributes(){
        return [];
    }

    public function embedOne($class, $link){
        $attribute = $this->getAttribute($link);
        /** @var MongoEmbedModel $obj */
        $obj = new $class;
        if(is_array($attribute))
            $obj->setAttributes($attribute);
        $obj->setParent($this);
        $obj->setAttribute($link);

        return $obj;
    }

    public function embedMany($class, $link){
        $attribute = $this->getAttribute($link);
        if(!is_array($attribute))return [];
        $result = [];
        foreach($attribute as $key => $item){
            /** @var MongoEmbedModel $obj */
            $obj = new $class;
            $obj->setAttributes($item);
            $obj->setParent($this);
            $obj->setAttribute($link);
            $result[$key] = $obj;
        }
        return $result;
    }

    public function embedManyById($class, $link){
        $attribute = $this->getAttribute($link);
        if(!is_array($attribute))return [];
        $result = [];
        foreach($attribute as $key => $item){
            /** @var MongoModel $obj */
            $obj = $class::findOne($item);
            $result[$key] = $obj;
        }
        return $result;
    }

    public function __get($name){
        $embedAttributes = $this->embedAttributes();
        if(!isset($embedAttributes[$name]))
            return parent::__get($name);
        if(isset($this->__embedItems[$name]))
            return $this->__embedItems[$name];
        $embedLink = $embedAttributes[$name];
        switch($embedLink['type']){
            case 'one':
                $entity = $this->embedOne($embedLink['class'], $name);
                break;
            case 'many':
                $entity = $this->embedMany($embedLink['class'], $name);
                break;
            case 'manyById':
                $entity = $this->embedManyById($embedLink['class'], $name);
                break;
        }
        $this->__embedItems[$name] = $entity;
        return $entity;
    }

    public function load($data, $fromName = null){
        $embed = $this->embedAttributes();
        if(is_null($fromName)){
            foreach($data as $scope => $iData){
                foreach($embed as $name => $em){
                    $className = explode('\\', $em['class']);
                    $className = $className[count($className) - 1];
                    if($className === $scope){
                        $this->$name->load($data);
                    }
                }
            }
        }else{
            foreach($embed as $name => $em){
                if($em['class'] === $fromName){
                    $this->$name->load($data);
                }
            }
        }
        return parent::load($data, $fromName);
    }

    public function __set($name, $value){
        $embedAttributes = $this->embedAttributes();
        if(!isset($embedAttributes[$name]))
            return parent::__set($name, $value);

        return $this->__embedItems[$name] = $value;
    }

    public function updateEmbed($attribute){
        if(isset($this->__embedItems[$attribute])){
            switch ($this->embedAttributes()[$attribute]['type']){
                case 'many':
                    $result = [];
                    foreach($this->__embedItems[$attribute] as $entity){
                        $result[] = $entity->toArray();
                    }
                    $this->setAttribute($attribute, $result);
                    break;
                case 'one':
                    $this->setAttribute($attribute, $this->__embedItems[$attribute]->toArray());
                    break;
                case 'manyById':
                    $result = [];
                    foreach($this->__embedItems[$attribute] as $entity){
                        $result[] = $entity->_id;
                    }
                    $this->setAttribute($attribute, $result);
                    break;
            }
        }
    }

    public function reloadEmbed(){
        foreach($this->embedAttributes() as $attribute => $t){
            $this->updateEmbed($attribute);
        }
    }
}
