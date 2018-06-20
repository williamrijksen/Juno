<?php

namespace Juno\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class JunoServiceProvider implements ServiceProviderInterface, ControllerProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['juno.mount_prefix'] = '/';
        $app['juno.base_url'] = function ($app) {
            return $app['juno.mount_prefix'];
        };

        $app->extend('twig.loader.filesystem', function (\Twig_Loader_Filesystem $loader) {
            $loader->addPath(__DIR__ . '/../Resources/views', 'Juno');

            return $loader;
        });
    }

    public function boot(Application $app)
    {
        $app->mount($app['juno.mount_prefix'], $this->connect($app));
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/queue.json', 'Juno\Controller\QueueController::indexAction')
            ->bind('juno_queue_index');

        $controllers->get('/queue/{queue}.json', 'Juno\Controller\QueueController::showAction')
            ->bind('juno_queue_show');

        $controllers->get('/retry/{hash}.json', 'Juno\Controller\QueueController::retryAction')
            ->bind('juno_queue_retry');

        $controllers->delete('/queue/{queue}.json', 'Juno\Controller\QueueController::deleteAction')
            ->bind('juno_queue_delete');

        $controllers->get('/template/{name}', 'Juno\Controller\DefaultController::templateAction')
            ->bind('juno_default_template');

        $controllers->get('/consumer.json', 'Juno\Controller\ConsumerController::indexAction')
            ->bind('juno_consumer_index');

        $controllers->get('/info.json', 'Juno\Controller\InfoController::indexAction')
            ->bind('juno_info_index');

        $controllers->get('/{url}', 'Juno\Controller\DefaultController::indexAction')
            ->bind('juno_homepage')->assert('url', '.{0,}?');

        return $controllers;
    }
}
