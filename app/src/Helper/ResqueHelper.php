<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ResqueHelper
{
    /**
     * Configure Resque
     *
     * @param ContainerInterface $container
     * @return array
     */
    public static function autoConfigure(ContainerInterface $container)
    {
        $redisHost = $container->getParameter('packages.resque.host');
        $redisPort = $container->getParameter('packages.resque.port');
        $redisDatabase = $container->getParameter('packages.resque.database');

        if (!isset($redisDatabase)) {
            $redisDatabase = 0;
        }

        $backend = strpos($redisHost, 'unix:') === false ? $redisHost . ':' . $redisPort : $redisHost;

        \Resque::setBackend($backend, $redisDatabase);

        return [
            'backend'  => $backend,
            'database' => $redisDatabase,
        ];
    }

    /**
     * Clear the given queue.
     *
     * @param string $queue The name of the queue
     *
     * @return int The number of removed items
     */
    public function clearQueue($queue)
    {
        $length = \Resque::redis()->llen('queue:' . $queue);
        \Resque::redis()->del('queue:' . $queue);

        return $length;
    }

    /**
     * Enqueue a job, but only if it is not already in the queue.
     *
     * @param string $queue
     * @param string $class
     * @param array $args
     * @param bool $trackStatus
     *
     * @return string The enqueued job ID
     */
    public function enqueueOnce($queue, $class, $args = null, $trackStatus = false)
    {
        foreach ($this->getJobs($queue) as $job) {
            if ($job->payload['class'] === $class) {
                return $job->payload['id'];
            }
        }

        return $this->enqueue($queue, $class, $args, $trackStatus);
    }

    /**
     * Get jobs for the given queue.
     *
     * @param string $queue The name of the queue or "*" for all queues
     *
     * @return array|\Resque_Job[]
     */
    public function getJobs($queue = null)
    {
        if (!$queue || $queue === '*') {
            $queues = \Resque::queues();
            $jobs = [];
            foreach ($queues as $queue) {
                $jobs = array_merge($jobs, $this->getJobs($queue));
            }

            return $jobs;
        }

        return array_map(function ($job) use ($queue) {
            return new \Resque_Job($queue, json_decode($job, true));
        }, \Resque::redis()->lrange('queue:' . $queue, 0, -1));
    }

    /**
     * Enqueue a job.
     *
     * @param string $queue
     * @param string $class
     * @param array $args
     * @param bool $trackStatus
     *
     * @return string The enqueued job ID
     */
    public function enqueue($queue, $class, $args = null, $trackStatus = false)
    {
        return \Resque::enqueue($queue, $class, $args, $trackStatus);
    }
}
