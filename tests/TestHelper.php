<?php

namespace Tests;

use Illuminate\Support\Facades\Queue;

class TestHelper
{
    /**
     * Mocks the Queue facades for testing purposes.
     *
     * This method fakes the Queue facade and sets up default behaviors for
     * the Queue::connection and Queue::pop methods. It is useful for unit
     * @param array $ToFake An optional array of jobs to fake.
     *
     * @return void
     */
    public static function mockQueueFacades($ToFake = []) : void
    {
        Queue::fake($ToFake);

        // Mock Queue facade
        Queue::shouldReceive('connection')
            ->andReturnSelf();

        // Default behavior for Queue::pop() (queue is empty)
        Queue::shouldReceive('pop')
            ->andReturn(null);
    }


    public static function mockQueue($data) : void
    {
        // Mock the Queue::pop() behavior for the command
        Queue::shouldReceive('pop')
            ->with('new-exception')
            ->andReturnUsing(function () use (&$data) {
                $item = $data->shift();
                if ($item === null) {
                    return null;
                }

                // Return a mock object with a payload() method
                return new class(json_encode(['payload' => $item->toArray()])) {
                    protected $payload;

                    public function __construct($payload)
                    {
                        $this->payload = $payload;
                    }

                    public function payload()
                    {
                        return json_decode($this->payload, true)['payload'];
                    }

                    //delete the message from the queue (mocked)
                    public function delete()
                    {
                        return true;
                    }
                };
            });
    }
}
