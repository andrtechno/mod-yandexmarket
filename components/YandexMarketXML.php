<?php

namespace panix\mod\yandexmarket\components;


use panix\mod\shop\components\AttributeData;
use Yii;
use panix\engine\Html;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use yii\helpers\Url;
/**
 * Exports products catalog to YML format.
 */
class YandexMarketXML {

    /**
     * @var int Maximum loaded products per one query
     */
    public $limit = 2;

    /**
     * @var string Default currency
     */
    public $currencyIso = 'UAH';

    /**
     * @var string
     */
    public $cacheFileName = 'yandex.market.xml';

    /**
     * @var string
     */
    public $cacheDir = '@runtime';

    /**
     * @var int
     */
    public $cacheTimeout = 86400;

    /**
     * @var resource
     */
    private $fileHandler;

    /**
     * @var integer
     */
    private $_config;

    /**
     * Initialize component
     */
    public function __construct() {
        $this->_config = Yii::$app->settings->get('yandexmarket');
        $this->currencyIso = Yii::$app->currency->getMain()->iso;
    }

    /**
     * Display xml file
     */
    public function processRequest() {
        $cache = Yii::$app->cache;
        $check = $cache->get($this->cacheFileName);
        if ($check === false) {
            $this->createXmlFile();
            if (!YII_DEBUG)
                $cache->set($this->cacheFileName, true, $this->cacheTimeout);
        }
        header("content-type: text/xml");
        echo file_get_contents($this->getXmlFileFullPath());
        exit;
    }

    /**
     * Create and write xml to file
     */
    public function createXmlFile() {
        $filePath = $this->getXmlFileFullPath();
        $this->fileHandler = fopen($filePath, 'w');

        $this->write("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n");
        $this->write("<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n");
        $this->write('<yml_catalog date="' . date('Y-m-d H:i') . '">');
        $this->write('<shop>');
        $this->renderShopData();
        $this->renderCurrencies();
        $this->renderCategories();
        $this->loadProducts();
        $this->write('</shop>');
        $this->write('</yml_catalog>');

        fclose($this->fileHandler);
    }

    /**
     * Write shop info
     */
    public function renderShopData() {
        $this->write('<name>' . $this->_config['name'] . '</name>');
        $this->write('<company>' . $this->_config['company'] . '</company>');
        $this->write('<url>' . $this->_config['url'] . '</url>');
    }

    /**
     * Write list of available currencies
     */
    public function renderCurrencies() {
        $this->write('<currencies>');
        $this->write('<currency id="' . $this->currencyIso . '" rate="1"/>');
        $this->write('</currencies>');
    }

    /**
     * Write categories to xm file
     */
    public function renderCategories() {
        $categories = Category::findOne(1)->children()->all();
        //print_r($categories);die;
        $this->write('<categories>');
        foreach ($categories as $c) {
            $parentId = null;
            $parent = $c->parent();

           // if ($parent && $parent->id != 1)
           //     $parentId = 'parentId="' . $parent->id . '"';
            $this->write('<category id="' . $c->id . '" ' . $parentId . '>' . Html::encode($c->name) . '</category>');
        }
        $this->write('</categories>');
    }

    /**
     * Write offers to xml file
     */
    public function loadProducts() {
        $limit = $this->limit;
        $total = ceil(Product::find()->published()->count() / $limit);
        $offset = 0;

        $this->write('<offers>');

        for ($i = 0; $i <= $total; ++$i) {
            $products = Product::find()
                //->where(['limit' => $limit, 'offset' => $offset])
                ->limit($limit)
                ->offset($offset)
                ->published()
                ->all();
            $this->renderProducts($products);

            $offset+=$limit;
        }

        $this->write('</offers>');
    }

    /**
     * @param array $products
     */
    public function renderProducts(array $products) {
        $data=[];
        foreach ($products as $p) {
            if (!count($p->variants)) {

                $data['url'] = Url::to($p->getUrl(),true);
                $data['price'] = Yii::$app->currency->convert($p->price, $this->_config['currency_id']);
                $data['name'] = Html::encode($p->name);
            } else {

                foreach ($p->variants as $v) {
                    $name = strtr('{product}({attr} {option})', array(
                        '{product}' => $p->name,
                        '{attr}' => $v->productAttribute->title,
                        '{option}' => $v->option->value
                    ));

                    $hashtag = '#' . $v->productAttribute->name . ':' . $v->option->id;
                    //TODO: need test product with variants
                    $data['url'] = Url::to($p->getUrl(),true).$hashtag;
                    $data['price'] = Yii::$app->currency->convert(Product::calculatePrices($p, $p->variants, 0), $this->_config['currency_id']);
                    $data['name'] = Html::encode($name);
                }
            }
            //Common options
            $data['categoryId'] = ($p->mainCategory) ? $p->mainCategory->id : false;
            $data['picture'] = $p->getImage() ? Url::to($p->getMainImage('100x100')->url,true) : null;
            $data['vendor'] = ($p->manufacturer) ? $p->manufacturer->name : false;
            $data['currencyId'] = $this->currencyIso;
            if(!empty($p->full_description)){
                $data['description'] = $this->clearText($p->full_description);
            }
            $attribute = new AttributeData($p);
            $test = $attribute->getData();

            foreach($test as $a){
                $data['param'][$a->name] = $a->value;
            }

            $this->renderOffer($p, $data);
        }
    }

    /**
     * @param Product $p
     * @param array $data
     */
    public function renderOffer(Product $p, array $data) {
        $available = ($p->availability == 1) ? 'true' : 'false';
        $this->write('<offer id="' . $p->id . '" available="' . $available . '">');

        foreach ($data as $key => $val) {
            if(is_array($val)){
                foreach($val as $name=>$value){
                    $this->write("<param name=\"".$name."\">" . $value . "</param>\n");
                }
            }else{
                $this->write("<$key>" . $val . "</$key>\n");
            }
        }
        $this->write('</offer>' . "\n");
    }

    /**
     * @param $text
     * @return string
     */
    public function clearText($text) {
        return '<![CDATA['  . $text  . ']]>';
    }

    /**
     * @return string
     */
    public function getXmlFileFullPath() {
        return Yii::getAlias($this->cacheDir) . DIRECTORY_SEPARATOR . $this->cacheFileName;
    }

    /**
     * Write part of xml to file
     * @param $string
     */
    private function write($string) {
        fwrite($this->fileHandler, $string);
    }

}
