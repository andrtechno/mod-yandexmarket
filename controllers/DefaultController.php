<?php

namespace panix\mod\yandexmarket\controllers;

use panix\mod\yandexmarket\components\YandexMarketXML;
use yii\web\Controller;

class DefaultController extends Controller {

    public function actionIndex() {
        $xml = new YandexMarketXML;
        $xml->processRequest();
    }

}
