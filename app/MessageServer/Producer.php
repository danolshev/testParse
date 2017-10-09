<?php

namespace app\app\MessageServer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
* 
*/
class Producer
{
	protected $connection; 

	function __construct(AMQPStreamConnection $connection)
	{
		$this->connection = $connection;
	}

	public function produce()
	{
		$channel = $this->connection->channel();
        $channel->queue_declare('parse', false, false, false, false);

        $msg = '{"type": "parse","data": {"site_id": 1,"url": "https://dealer.equip.center/153222/"}}';

        $message = new AMQPMessage($msg);

        $channel->basic_publish($message, '', 'parse');

        echo "Sent message";

        $channel->close();
        $this->connection->close();
	}
}