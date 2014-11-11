<?php

namespace app\commands;

use Yii;
use app\backend\models\ErrorMonitorConfig;
use app\models\ErrorLog;
use yii\console\Controller;

class ErrornotifierController extends Controller
{
    public function actionNotify()
    {
        $errorMonitorCondfig = new ErrorMonitorConfig();

        $notifyEnabled = $errorMonitorCondfig->emailNotifyEnabled;
        if ($notifyEnabled == 0 || null == $notifyEnabled) {
            return;
        }

        $email = $errorMonitorCondfig->devmail;
        $errorCodes = explode(",", $errorMonitorCondfig->notifyOnlyHttpCodes);

        if (null == $email) {
            return;
        }

        $oneDayAgoTimestamp = strtotime("-1 day", time());

        $errorLog = new ErrorLog();
        $rows = $errorLog->find()->where('timestamp > :timestamp', [':timestamp' => $oneDayAgoTimestamp])->all();

        $result = [];
        foreach ($rows as $row) {
            if (strlen($errorCodes[0]) > 0) {
                if (!in_array($row->http_code, $errorCodes)) {
                    continue;
                }
            }

            $errUrl = $row->getErrorUrl()->one();
            if (null != $errUrl) {
                $result[$errUrl->url][date(\DateTime::RFC2822, $row->timestamp)]['info'] = $row->info;
                $result[$errUrl->url][date(\DateTime::RFC2822, $row->timestamp)]['server_vars'] = $row->server_vars;
                $result[$errUrl->url][date(\DateTime::RFC2822, $row->timestamp)]['request_vars'] = $row->request_vars;

                $result[$errUrl->url][date(\DateTime::RFC2822, $row->timestamp)]['http_code'] = $row->http_code;
            }
        }

        Yii::$app->mail->compose(
            '@app/views/notifications/error_notify.php',
            [
                'info' => $result
            ]
        )
            ->setTo($email)
            ->setFrom(Yii::$app->mail->transport->getUsername())
            ->setSubject("ErrorMonnitor notify")
            ->send();
    }
}
