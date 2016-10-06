<?php
/**
 * @author Valentin Konusov <rlng-krsk@yandex.ru>
 */

namespace yiidreamteam\platron\actions;

use yii\base\Action;
use yii\base\InvalidConfigException;
use matperez\yii2platron\Platron;

class ResultAction extends Action
{
    public $componentName;

    public $redirectUrl;

    public $silent = false;

    /** @var \matperez\yii2platron\Platron */
    private $api;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        assert(isset($this->componentName));
        assert(isset($this->redirectUrl));

        $this->api = \Yii::$app->get($this->componentName);

        if (!$this->api instanceof Platron) {
            throw new InvalidConfigException('Invalid Platron component configuration');
        }
    }

    public function run()
    {
        try {
            $response = $this->api->processResult(\Yii::$app->request->post());
        } catch (\Exception $e) {
            throw $e;
        }

        Platron::sendXlmResponse($response);
    }
}