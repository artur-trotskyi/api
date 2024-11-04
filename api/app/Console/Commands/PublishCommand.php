<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a message to RabbitMQ and wait for a response';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = null;
        $channel = null;

        try {
            // Connect to RabbitMQ
            $connection = new AMQPStreamConnection('rabbitmq', 5672, 'docker', 'docker');
            $channel = $connection->channel();

            // Declare a temporary queue for responses
            [$callbackQueue] = $channel->queue_declare('', false, false, true, false);

            $correlationId = Uuid::uuid4()->toString();

            // Create a request with correlation_id and reply queue
            $msg = new AMQPMessage(
                json_encode(['action' => 'get_token']),
                [
                    'correlation_id' => $correlationId,
                    'reply_to' => $callbackQueue,
                ]
            );

            // Publish the message to the 'auth' exchange
            $channel->basic_publish($msg, '', 'auth');
            $this->info("[x] Sent request with Correlation ID: {$correlationId}");

            $response = null;

            // Callback function to handle responses
            $callback = function (AMQPMessage $msg) use (&$response, $correlationId): void {
                if ($msg->get('correlation_id') === $correlationId) {
                    $response = json_decode($msg->getBody(), true);
                }
            };

            // Consume responses from the temporary queue
            $channel->basic_consume($callbackQueue, '', false, true, false, false, $callback);

            // Wait for a response without timeout
            while ($response === null) {
                try {
                    // Wait for messages with a timeout of 5 seconds
                    $channel->wait(null, false, 5);
                } catch (AMQPTimeoutException $e) {
                    $this->info('[x] Timeout waiting for response.');
                    break;
                }
            }

            // Output the received response
            if ($response) {
                $this->info('[x] Received response: ' . print_r($response, true));
            } else {
                $this->info('[x] No response received.');
            }
        } catch (Exception $e) {
            // Handle any exceptions that occur during processing
            $this->error('[x] Error: ' . $e->getMessage());

            return self::FAILURE;
        } finally {
            // Close the channel and connection
            $this->closeChannelAndConnection($channel, $connection);

            return self::SUCCESS;
        }
    }

    private function closeChannelAndConnection($channel, $connection): void
    {
        // Close the channel if it exists
        if ($channel) {
            try {
                $channel->close();
            } catch (Exception $e) {
                $this->error('[x] Error closing channel: ' . $e->getMessage());
            }
        }
        // Close the connection if it exists
        if ($connection) {
            try {
                $connection->close();
            } catch (Exception $e) {
                $this->error('[x] Error closing connection: ' . $e->getMessage());
            }
        }
    }
}
