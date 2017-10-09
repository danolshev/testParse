<?php

namespace app\MessageServer;

use app\Parse\Parser;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use app\models\ResultParse;

/**
* 
*/
class Consumer
{	
	protected $connection;

	function __construct(AMQPStreamConnection $connection)
	{
		$this->connection = $connection;
	}

	public function consume()
	{
        $channel = $this->connection->channel();
        $channel->queue_declare('parse', false, false, false, false);

        echo "Waiting message";

        $callback = function($message) {
            $message = json_decode($message->body);

            $parse = new Parser($message->data->url);
            $resultParse = new ResultParse();
            $resultParse->json = $parse->parse();
            $resultParse->save();
        };

        $channel->basic_consume('parse', '', false, true, false, false, $callback);
        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
	}
}