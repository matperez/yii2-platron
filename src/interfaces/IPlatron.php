<?php
namespace matperez\yii2platron\interfaces;

interface IPlatron
{
    /**
     * @return IApi
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public function getApi();
}