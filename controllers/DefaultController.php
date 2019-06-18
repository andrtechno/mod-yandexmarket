<?php

namespace panix\mod\yandexmarket\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use panix\mod\yandexmarket\components\YandexMarketXML;

class DefaultController extends Controller {

    public function actionIndex() {

        Yii::$app->log->targets['file1']->enabled = false;
        Yii::$app->log->targets['file2']->enabled = false;
        Yii::$app->log->targets['file3']->enabled = false;
        Yii::$app->log->targets['file4']->enabled = false;
        //Yii::$app->response->format = Response::FORMAT_XML;
        $xml = new YandexMarketXML;
        $xml->processRequest();
    }

}
