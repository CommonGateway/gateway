<?php

namespace App\Subscriber;

use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['request'],
        ];
    }

    /**
     * @param ResponseEvent $event
     */
    public function request(ResponseEvent $event)
    {
        $response = $event->getResponse();

        // Set multiple headers simultaneously
        $response->headers->add([
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }
}
