<?php

namespace panix\mod\yandexmarket\models;

use Yii;
use panix\engine\SettingsModel;

class SettingsForm extends SettingsModel
{

    protected $module = 'yandexmarket';

    public $name;
    public $company;
    public $url;

    public static function defaultSettings()
    {
        return [
            'name' => Yii::$app->settings->get('app', 'sitename'),
            'company' => 'Демо кампания',
            'url' => Yii::$app->request->hostInfo,
        ];
    }

    public function rules()
    {
        return [
            [['name', 'company', 'url'], 'string'],
            [['name', 'company', 'url'], 'required'],
        ];
    }

}
