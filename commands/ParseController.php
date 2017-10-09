<?php
namespace app\commands;

use yii\console\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use app\models\ResultParse;
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

        $channel = $connection->channel();
        $channel->queue_declare('parse', false, false, false, false);

        $msg = '{"type": "parse","data": {"site_id": 1,"url": "https://dealer.equip.center/153222/"}}';

        $message = new AMQPMessage($msg);

        $channel->basic_publish($message, '', 'parse');

        echo "Sent message";

        $channel->close();
        $connection->close();
    }
    
    public function actionConsumer()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('parse', false, false, false, false);

        echo "Waiting message";

        $callback = function($message) {
            $resultParse = new ResultParse();
            $resultParse->json = $this->parse($message->body);
            $resultParse->save();
        };

        $channel->basic_consume('parse', '', false, true, false, false, $callback);
        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    private function parse($message)
    {
        $message = json_decode($message);
        $siteContent = file_get_contents($message->data->url);
        $siteUrl = parse_url($message->data->url, PHP_URL_HOST);

        $title = [];
        preg_match('/<h1 class="detail">(.*)<\/h1>/s', $siteContent, $title);
        $description = [];
        preg_match('/id="productTabContent".*<p>(.*)<\/p>/Us', $siteContent, $description);
        $carousel = [];
        preg_match('/id="detail-photo-carousel"(.*)id="product_desc"/Us', $siteContent, $carousel);
        $imagesSrc = [];
        
        preg_match_all('/<img.*src="(.*)"/Us', $carousel[1], $imagesSrc);
        $imagesUrl = [];
        foreach ($imagesSrc[1] as $imageSrc) {
            $imagesUrl[] = $siteUrl . $imageSrc;
        }
        $desc = [];
        preg_match('/id="product_desc".*table>/Us', $siteContent, $desc);
        
        $attrs = [];
        preg_match_all('/<tr>.*<td>(.*)\((.*)\)<\/td>.*<td>(.*)<\/td>/Us', $desc[0], $attrs, PREG_SET_ORDER);
        $resultAttrs = [];
        foreach ($attrs as $attr) {
            $temp = [];
            $temp['name'] = trim($attr[1]);
            $temp['value'] = trim($attr[3]);
            $temp['unit'] = trim($attr[2]);
            $resultAttrs[] = $temp;
        }
        $result = [
            'id' => preg_replace('/\//', '', parse_url($message->data->url, PHP_URL_PATH)),
            'url' => $message->data->url,
            'created_at' => '',
            'title' => $title[1],
            'description' => $description[1],
            'sku' => '',
            'price' => '',
            'currency' => '',
            'images' => $imagesUrl,
            'attrs' => $resultAttrs,
        ];

        return json_encode($result);
    }
}
