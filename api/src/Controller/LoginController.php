<?php

// src/Controller/LoginController.php

namespace App\Controller;

use App\Entity\ObjectEntity;
use App\Service\EavService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
Use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class LoginController.
 *
 *
 * @Route("/")
 */
class LoginController extends AbstractController
{

    private CacheInterface $cache;
    private EntityManagerInterface $entityManager;

    public function __construct(CacheInterface $cache, EntityManagerInterface $entityManager)
    {
        $this->cache = $cache;
        $this->entityManager = $entityManager;
    }


    public function getObject(string $uri, EavService $eavService): array
    {
        $object = $this->entityManager->getRepository('App:ObjectEntity')->findOneBy(['uri' => $uri]);
        if($object instanceof ObjectEntity){
            return $eavService->renderResult($object);
        }
        return [];
    }
    /**
     * @Route("/me")
     */
    public function MeAction(Request $request, CommonGroundService $commonGroundService, EavService $eavService)
    {

        if ($this->getUser()) {
            $result = [
                'first_name' => $this->getUser()->getFirstName(),
                'last_name' => $this->getUser()->getLastName(),
                'name' => $this->getUser()->getName(),
                'email' => $this->getUser()->getEmail(),
                'roles' => $this->getUser()->getRoles(),
            ];
            if($this->getUser()->getOrganization()){
                $result['organization'] = $this->getObject($this->getUser()->getOrganization(), $eavService) ?? $commonGroundService->getResource($this->getUser()->getOrganization());

            }
            if($this->getUser()->getPerson()){
                $result['person'] = $this->getObject($this->getUser()->getPerson(), $eavService) ?? $commonGroundService->getResource($this->getUser()->getPerson());
            }
            $result = json_encode($result);
        } else {
            $result = null;
        }

        return new Response(
            $result,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );

    }


}
