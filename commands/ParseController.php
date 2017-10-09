<?php
namespace app\commands;

use yii\console\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use app\MessageServer\Consumer;
use app\MessageServer\Produder;
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
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $producer = new Producer($Connection);
        $producer->produce();
    }
    
    public function actionConsumer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $consumer = new Consumer($connection);
        $consumer->consume();
    }
}
