<?php
namespace app\commands;

use yii\console\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use app\app\MessageServer\Consumer;
use app\app\MessageServer\Producer;
/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author danolshev
 * @since 2.0
 */
class ParseController extends Controller
{
    
    public function actionProducer()
    {
        $connection = new AMQPStreamConnection(\Yii::$app->params['rabbitMQ']['host'], \Yii::$app->params['rabbitMQ']['port'], \Yii::$app->params['rabbitMQ']['user'], \Yii::$app->params['rabbitMQ']['password']);
        $producer = new Producer($connection);
        $producer->produce();
    }
    
    public function actionConsumer()
    {
        $connection = new AMQPStreamConnection(\Yii::$app->params['rabbitMQ']['host'], \Yii::$app->params['rabbitMQ']['port'], \Yii::$app->params['rabbitMQ']['user'], \Yii::$app->params['rabbitMQ']['password']);
        $consumer = new Consumer($connection);
        $consumer->consume();
    }
}
