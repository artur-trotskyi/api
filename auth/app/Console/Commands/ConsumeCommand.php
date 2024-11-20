<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from RabbitMQ and process them';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Connect to RabbitMQ
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'docker', 'docker');
        $channel = $connection->channel();

        // Declare the 'auth' queue, with parameters indicating that the queue should be durable
        $channel->queue_declare('auth', false, true, false, false);

        $this->info("[x] Waiting for messages in 'auth' queue...");

        // Callback function to process incoming messages
        $callback = function (AMQPMessage $msg) use ($channel): void {
            $this->info('[x] Received message: '.$msg->getBody());

            // Process the request (e.g., issuing a token)
            $response = ['token' => bin2hex(random_bytes(16))];

            // Create a response message with the same correlation_id
            $replyMsg = new AMQPMessage(
                json_encode($response),
                ['correlation_id' => $msg->get('correlation_id')]
            );

            // Send the response to the queue specified in reply_to
            $channel->basic_publish($replyMsg, '', $msg->get('reply_to'));
            $this->info('[x] Sent response for Correlation ID: '.$msg->get('correlation_id'));
        };

        // Consume messages from the 'auth' queue
        $channel->basic_consume('auth', '', false, true, false, false, $callback);

        // Wait for messages
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        // Close the channel and connection
        $this->closeChannelAndConnection($channel, $connection);
    }

    private function closeChannelAndConnection($channel, $connection): void
    {
        // Close the channel if it exists
        if ($channel) {
            try {
                $channel->close();
            } catch (Exception $e) {
                $this->error('[x] Error closing channel: '.$e->getMessage());
            }
        }
        // Close the connection if it exists
        if ($connection) {
            try {
                $connection->close();
            } catch (Exception $e) {
                $this->error('[x] Error closing connection: '.$e->getMessage());
            }
        }
    }
}
