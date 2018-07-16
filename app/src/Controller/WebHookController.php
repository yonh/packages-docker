<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Controller;

use Nice\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terramar\Packages\Event\PackageUpdateEvent;
use Terramar\Packages\Events;
use Terramar\Packages\Helper\ResqueHelper;

class WebHookController extends ContainerAwareController
{
    public function receiveAction(Application $app, Request $request, $id)
    {
        ResqueHelper::autoConfigure($this->container);

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app->get('doctrine.orm.entity_manager');
        $package = $entityManager->getRepository('Terramar\Packages\Entity\Package')->findOneBy([
            'id'      => $id,
            'enabled' => true,
        ]);
        if (!$package || !$package->isEnabled() || !$package->getRemote()->isEnabled()) {
            return new Response('Project not found', 404);
        }

        $receivedData = json_decode($request->getContent());
        $event = new PackageUpdateEvent($package, $receivedData);

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = $app->get('event_dispatcher');
        $dispatcher->dispatch(Events::PACKAGE_UPDATE, $event);

        // GitLab 8.3 accepts only "200" as a successful receipt.
        return new Response('OK', 200);
    }
}
