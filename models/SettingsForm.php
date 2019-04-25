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
    public $currency_id;

    public static function defaultSettings()
    {
        return [
            'name' => Yii::$app->settings->get('app', 'site_name'),
            'company' => 'Демо кампания',
            'url' => Yii::$app->request->hostInfo,
            'currency_id' => null,
        ];
    }

    public function validateCurrency()
    {
        $currencies = Yii::$app->currency->getCurrencies();
        if (count($currencies)) {
            if (!array_key_exists($this->currency_id, $currencies))
                $this->addError('currency_id', self::t('ERROR_CURRENCY'));
        }
    }

    public function rules()
    {
        return [
            ['currency_id', 'validateCurrency'],
            [['name', 'company', 'url'], 'string'],
            [['name', 'company', 'url'], 'required'],
        ];
    }

    public function getCurrencies()
    {
        $result = array();
        foreach (Yii::$app->currency->getCurrencies() as $id => $model)
            $result[$id] = $model->name;
        return $result;
    }

}
