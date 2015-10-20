<?php
namespace app\modules\seo\handlers;

use app\modules\seo\controllers\ManageController;
use app\modules\seo\models\Counter;
use app\modules\seo\SeoModule;
use yii\base\ActionEvent;
use yii\base\Object;
use yii\web\View;

class AnalyticsHandler extends Object
{
    static public function handleBeforeAction(ActionEvent $event)
    {
        /** @var SeoModule $seoModule */
        $seoModule = \Yii::$app->getModule('seo');

        if ('payment' === $event->action->controller->id && 'success' === $event->action->id) {
            \Yii::$app->getView()->on(
                View::EVENT_END_BODY,
                [Counter::className(), 'renderCounters'],
                [$event->action->controller->module->id . '/' . $event->action->controller->id]
            );
            \Yii::$app->getView()->on(
                View::EVENT_END_BODY,
                [ManageController::className(), 'renderEcommerceCounters'],
                ['transactionId' => intval(\Yii::$app->request->get('id'))]
            );
        }

        if (1 === intval($seoModule->analytics['ecGoogle'])) {
            GoogleEcommerceHandler::installHandlers($event);
        }

        if (1 === intval($seoModule->analytics['ecYandex'])) {
            YandexEcommerceHandler::installHandlers($event);
        }
    }
}