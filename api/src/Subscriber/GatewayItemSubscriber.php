<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Gateway;
use App\Entity\Registration;
use App\Service\GatewayService;
use App\Service\LayerService;
use App\Service\NewRegistrationService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Conduction\CommonGroundBundle\Service\SerializerService;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class GatewayItemSubscriber implements EventSubscriberInterface
{

    private GatewayService $gatewayService;

    /**
     * UserItemSubscriber constructor.
     *
     */
    public function __construct(GatewayService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['gateway', EventPriorities::PRE_DESERIALIZE],
        ];
    }

    /**
     * @param RequestEvent $event
     *
     * @throws Exception
     */
    public function gateway(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $route = $event->getRequest()->attributes->get('_route');

        if (
            $route !== 'api_gateways_gateway_get_item' &&
            $route !== 'api_gateways_gateway_put_item' &&
            $route !== 'api_gateways_gateway_delete_item'
        ) {
            return;
        }

        $response = $this->gatewayService->processGateway(
            $event->getRequest()->attributes->get('name'),
            $event->getRequest()->attributes->get('endpoint'),
            $event->getRequest()->getMethod(),
            $event->getRequest()->getContent(),
            $event->getRequest()->query->all(),
        );

        $event->setResponse($response);

    }

}
