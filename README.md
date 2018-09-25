# yii2-mongodb-embedded
Yii2 extension to work with embedded documents in MongoDB.<br /><br />
<br />
This extension requires MongoDB PHP Extension version 1.0.0 or higher.<br />
This extension requires MongoDB server version 3.0 or higher.<br />
## Installation
The preferred way to install this extension is through composer.<br />
Either run<br />
```
php composer.phar require --prefer-dist astraller/yii2-mongodb-embedded
```
or add<br />
```
"astraller/yii2-mongodb-embedded": "^1.*"
```
to the require section of your composer.json.<br />

## Usage
- Your model must be inherited from astraller\mongodb\MongoModel class.
- Add embedAttributes method
```php
    public function embedAttributes() {
        return [
            'conditions' => [
                'class' => DialogConditions::class,
                'type'  => 'many',
            ],
            'text'       => [
                'class' => Text::class,
                'type'  => 'one',
            ],
            'variants' => [
                'class' => Dialog::class,
                'type' => 'manyById'
            ]
        ];
    }
```
- Your embedded documents must be inherited from EmbeddedDocument class.
```php
<?php
namespace app\models;

use astraller\mongodb\MongoEmbedModel;

class Vector3 extends MongoEmbedModel
{

    public $x;
    public $y;
    public $z;

    public function rules()
    {
        return [
            [['x', 'y', 'z'], 'required'],
        ];
    }

    public function t(){
        return "[X: {$this->x}; Y: {$this->y}; Z: {$this->z}]";
    }

}
```
