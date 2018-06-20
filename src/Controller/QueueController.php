<?php

namespace Juno\Controller;

use Bernard\Driver\PhpRedisDriver;
use Bernard\Envelope;
use Bernard\Producer;
use Bernard\QueueFactory;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Juno\EnvelopeIterator;

class QueueController
{
    public function indexAction(Application $app, $_format = '')
    {
        $driver = $app['bernard.driver'];
        $queues = array();

        foreach ($app['bernard.driver']->listQueues() as $queue) {
            $queues[] = array('name' => $queue, 'count' => $driver->countMessages($queue));
        }

        return $app->json($queues);
    }

    public function showAction(Application $app, Request $request, $queue)
    {
        $offset = $request->query->get('offset', 1);
        $limit  = $request->query->get('limit', 50);

        $queue = $app['bernard.queue_factory']->create($queue);
        $envelopes = new EnvelopeIterator($queue->peek($offset, $limit));

        return $app->json(array(
            'name' => (string) $queue,
            'count' => $queue->count(),
            'messages' => iterator_to_array($envelopes),
        ));
    }

    public function retryAction(Application $app, Request $request)
    {
        /** @var Producer $producer*/
        $producer = $app['bernard.producer'];
        $offset = $request->query->get('offset', 1);
        $limit  = $request->query->get('limit', 50);

        /** @var QueueFactory $queueFactory */
        $queueFactory = $app['bernard.queue_factory'];
        $queue = $queueFactory->create('failed');

        /** @var Envelope $envelope */
        foreach ($queue->peek($offset, $limit) as $envelope) {
            $producer->produce(
                $envelope->getMessage(), $queue
            );
        }

        return $app->json(array(), 204);
    }

    public function deleteAction(Application $app, $queue)
    {
        /** @var PhpRedisDriver $driver */
        $driver = $app['bernard.driver'];
        $driver->removeQueue($queue);

        return $app->json(array(), 204);
    }
}
