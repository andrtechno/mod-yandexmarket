<?php

namespace panix\mod\yandexmarket\components;

use Yii;
use yii\helpers\Url;
use panix\engine\Html;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\shop\components\AttributeData;

/**
 * Exports products catalog to YML format.
 */
class YandexMarketXML
{

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
    public function __construct()
    {
        $this->_config = Yii::$app->settings->get('yandexmarket');
        $this->currencyIso = Yii::$app->currency->getMain()->iso;
    }

    /**
     * Display xml file
     */
    public function processRequest()
    {
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
    public function createXmlFile()
    {
        $filePath = $this->getXmlFileFullPath();
        $this->fileHandler = fopen($filePath, 'w');

        $this->write("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" . PHP_EOL);
        $this->write("<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n" . PHP_EOL);
        $this->write('<yml_catalog date="' . date('Y-m-d H:i') . '">' . PHP_EOL);
        $this->write('<shop>' . PHP_EOL);
        $this->renderShopData();
        $this->renderCurrencies();
        $this->renderCategories();
        $this->loadProducts();
        $this->write('</shop>' . PHP_EOL);
        $this->write('</yml_catalog>' . PHP_EOL);

        fclose($this->fileHandler);
    }

    /**
     * Write shop info
     */
    public function renderShopData()
    {
        $this->write('<name>' . $this->_config->name . '</name>' . PHP_EOL);
        $this->write('<company>' . $this->_config->company . '</company>' . PHP_EOL);
        $this->write('<version>' . Yii::$app->getVersion() . '</version>' . PHP_EOL);
        $this->write('<platform>' . Yii::$app->name . '</platform>' . PHP_EOL);
        $this->write('<url>' . $this->_config->url . '</url>' . PHP_EOL);
        $this->write('<email>' . Yii::$app->settings->get('app', 'email') . '</email>' . PHP_EOL);
    }

    /**
     * Write list of available currencies
     */
    public function renderCurrencies()
    {
        $this->write('<currencies>' . PHP_EOL);
        $this->write('<currency id="' . $this->currencyIso . '" rate="1"/>' . PHP_EOL);
        $this->write('</currencies>' . PHP_EOL);
    }

    /**
     * Write categories to xm file
     */
    public function renderCategories()
    {
        $categories = Category::find()->excludeRoot()->all();
        $this->write('<categories>' . PHP_EOL);

        foreach ($categories as $c) {
            $parentId = null;
            $parent = $c->parent()->one(); //getparent()
            if ($parent && $parent->id != 1)
                $parentId = 'parentId="' . $parent->id . '"';
            $this->write('<category id="' . $c->id . '" ' . $parentId . '>' . Html::encode($c->name) . '</category>' . PHP_EOL);
        }
        $this->write('</categories>' . PHP_EOL);
    }

    /**
     * Write offers to xml file
     */
    public function loadProducts()
    {

        $total = ceil(Product::find()->published()->count() / $this->limit);
        $offset = 0;

        $this->write('<offers>');

        for ($i = 0; $i <= $total; ++$i) {
            $products = Product::find()
                //->where(['limit' => $limit, 'offset' => $offset])
                ->limit($this->limit)
                ->offset($offset)
                ->published()
                ->all();

            $this->renderProducts($products);

            $offset += $this->limit;
        }

        $this->write('</offers>');
    }

    /**
     * @param array $products
     */
    public function renderProducts(array $products)
    {
        $data = [];
        foreach ($products as $p) {
            if (!count($p->variants)) {

                $data['url'] = Url::to($p->getUrl(), true);
                $data['price'] = Yii::$app->currency->convert($p->price, $this->_config->currency_id);
                $data['name'] = Html::encode($p->name);
                $data['vendor'] = ($p->manufacturer) ? $p->manufacturer->name : false;

                //  $attribute = new AttributeData($p);
                //  $test = $attribute->getData();
                // $data['params'] = [];
                // foreach ($test as $a) {
                //     $data['params'][$a->name] = $a->value;
                //}


            } else {

                foreach ($p->variants as $v) {
                    $name = strtr('{product}({attr} {option})', [
                        '{product}' => $p->name,
                        '{attr}' => $v->productAttribute->title,
                        '{option}' => $v->option->value
                    ]);

                    $hashtag = '#' . $v->productAttribute->name . ':' . $v->option->id;
                    //TODO: need test product with variants
                    $data['url'] = Url::to($p->getUrl(), true) . $hashtag;
                    $data['price'] = Yii::$app->currency->convert(Product::calculatePrices($p, $p->variants, 0), $this->_config->currency_id);
                    $data['name'] = Html::encode($name);
                }
            }
            //Common options
            $data['categoryId'] = ($p->mainCategory) ? $p->mainCategory->id : false;
            $data['vendor'] = ($p->manufacturer) ? $p->manufacturer->name : false;
            $data['currencyId'] = $this->currencyIso;
            $data['model'] = Html::encode($p->sku);
            if (!empty($p->full_description)) {
                $data['description'] = $this->clearText($p->full_description);
            }

            //$attribute = new AttributeData($p);
            //$test = $attribute->getData();

            //foreach ($test as $a) {
            ///    $data['param'][$a->name] = $a->value;
            //}
            $data['images'] = [];
            foreach ($p->images as $img) {
                 $data['images'][] = Url::to($img->getUrl(),true);
            }
            $this->renderOffer($p, $data);
        }
    }

    /**
     * @param Product $p
     * @param array $data
     */
    public function renderOffer(Product $p, array $data)
    {
        $available = ($p->availability == 1) ? 'true' : 'false';
        $this->write('<offer id="' . $p->id . '" available="' . $available . '">' . PHP_EOL);

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                if ($key == 'params') {
                    foreach ($val as $name => $value) {
                        $this->write("<param name=\"" . $name . "\">" . $value . "</param>" . PHP_EOL);
                    }
                } elseif ($key == 'images') {
                    foreach ($val as $name => $value) {
                        $this->write("<picture>" . $value . "</picture>" . PHP_EOL);
                    }
                }
            } else {
                $this->write("<$key>" . $val . "</$key>" . PHP_EOL);
            }
        }
        $this->write('</offer>' . PHP_EOL);
    }

    /**
     * @param $text
     * @return string
     */
    public function clearText($text)
    {
        return '<![CDATA[' . $text . ']]>';
    }

    /**
     * @return string
     */
    public function getXmlFileFullPath()
    {
        return Yii::getAlias($this->cacheDir) . DIRECTORY_SEPARATOR . $this->cacheFileName;
    }

    /**
     * Write part of xml to file
     * @param $string
     */
    private function write($string)
    {
        fwrite($this->fileHandler, $string);
    }

}
