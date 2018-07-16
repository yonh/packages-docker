<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Console\Command\Worker;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Terramar\Packages\Console\Command\ContainerAwareCommand;
use Terramar\Packages\Helper\ResqueHelper;

class StopCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('resque:worker:stop')
            ->setDescription('Stop a resque worker')
            ->addArgument('id', InputArgument::OPTIONAL, 'Worker id')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Should kill all workers')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force kill all workers, immediately');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ResqueHelper::autoConfigure($this->container);

        if ($input->getOption('all')) {
            $workers = \Resque_Worker::all();
        } else {
            $worker = \Resque_Worker::find($input->getArgument('id'));

            if (!$worker) {
                $availableWorkers = \Resque_Worker::all();
                if (!empty($availableWorkers)) {
                    throw new \RuntimeException('A running worker must be specified');
                }
            }

            $workers = $worker ? [$worker] : [];
        }

        if (count($workers) <= 0) {
            $output->writeln([
                'No workers running',
                '',
            ]);

            return;
        }

        $signal = $input->getOption('force') ? SIGTERM : SIGQUIT;
        foreach ($workers as $worker) {
            $output->writeln(sprintf('%s %s...', $signal === SIGTERM ? 'Force stopping' : 'Stopping', $worker));
            list(, $pid) = explode(':', (string)$worker);

            posix_kill($pid, $signal);
        }

        $output->writeln('');
    }
}
