<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LogService
{
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;

    public function __construct(
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    /**
     * Creates or updates a Log object with current request and response or given content.
     *
     * @param Request       $request   The request to fill this Log with.
     * @param Response|null $response  The response to fill this Log with.
     * @param string|null   $content   The content to fill this Log with if there is no response.
     * @param bool|null     $finalSave
     * @param string        $type
     *
     * @return Log
     */
    public function saveLog(Request $request, Response $response = null, string $content = null, bool $finalSave = null, string $type = 'in'): Log
    {
        $existingLog = $this->getLog($response);
        $existingLog ? $callLog = $existingLog : $callLog = new Log();

        $callLog->setType($type);
        $callLog->setRequestMethod($request->getMethod());
        $callLog->setRequestHeaders($request->headers->all());
        $callLog->setRequestQuery($request->query->all() ?? null);
        $callLog->setRequestPathInfo($request->getPathInfo());
        $callLog->setRequestLanguages($request->getLanguages() ?? null);
        $callLog->setRequestServer($request->server->all());
        $callLog->setRequestContent($request->getContent());

        $response && $callLog = $this->updateLogResponse($response, $callLog, false);

        if ($content) {
            $callLog->setResponseContent($content);
            // @todo Cant set response content if content is pdf
        } elseif ($response && !(is_string($response->getContent()) && strpos($response->getContent(), 'PDF'))) {
            $callLog->setResponseContent($response->getContent());
        }

        $routeName = $request->attributes->get('_route') ?? null;
        $routeParameters = $request->attributes->get('_route_params') ?? null;
        $callLog->setRouteName($routeName);
        $callLog->setRouteParameters($routeParameters);

        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        $callLog->setResponseTime(intval($time * 1000));

        if ($this->session) {
            // add before removing
            $callLog->setCallId($this->session->get('callId'));
            $callLog->setSession($this->session->getId());

            if ($this->session->get('endpoint')) {
                $endpoint = $this->entityManager->getRepository('App:Endpoint')->findOneBy(['id' => $this->session->get('endpoint')]);
            }
            $callLog->setEndpoint(!empty($endpoint) ? $endpoint : null);
            if ($this->session->get('entity')) {
                $entity = $this->entityManager->getRepository('App:Entity')->findOneBy(['id' => $this->session->get('entity')]);
            }
            $callLog->setEntity(!empty($entity) ? $entity : null);
            if ($this->session->get('source')) {
                $source = $this->entityManager->getRepository('App:Gateway')->findOneBy(['id' => $this->session->get('source')]);
            }
            $callLog->setGateway(!empty($source) ? $source : null);
            if ($this->session->get('handler')) {
                $handler = $this->entityManager->getRepository('App:Handler')->findOneBy(['id' => $this->session->get('handler')]);
            }
            $callLog->setHandler(!empty($handler) ? $handler : null);

            // remove before setting the session values
            //            if ($finalSave === true) {
            //                $this->session->remove('callId');
            //                $this->session->remove('endpoint');
            //                $this->session->remove('entity');
            //                $this->session->remove('source');
            //                $this->session->remove('handler');
            //            }

            // Set session values without relations we already know
            // $sessionValues = $this->session->all();
            // unset($sessionValues['endpoint']);
            // unset($sessionValues['source']);
            // unset($sessionValues['entity']);
            // unset($sessionValues['endpoint']);
            // unset($sessionValues['handler']);
            // unset($sessionValues['application']);
            // unset($sessionValues['applications']);
            // $callLog->setSessionValues($sessionValues);
        }
        $this->entityManager->persist($callLog);
        $this->entityManager->flush();

        return $callLog;
    }

    private function getLog($type = null)
    {

        $logRepo = $this->entityManager->getRepository('App:Log');

        if ($this->session->get('callId') !== null) {
            if (isset($type) && $type !== 'in') {
                return null;
            }

            return $logRepo->findOneBy(['callId' => $this->session->get('callId'), 'type' => $type]);
        }

        return null;
    }

    public function updateLogResponse(Response $response, Log $log = null, bool $persist = false, $test = false)
    {
        !$log && $log = $this->getLog($response);

        $log->setResponseStatus($this->getStatusWithCode($response->getStatusCode()));
        $test ?
            $log->setResponseStatusCode(1234) :
            $log->setResponseStatusCode($response->getStatusCode());
        $log->setResponseHeaders($response->headers->all());

        if ($persist) {
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } else {
            return $log;
        }
    }

    public function makeRequest(): Request
    {
        return new Request(
            $_GET,
            $_POST,
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }

    public function getStatusWithCode(int $statusCode): ?string
    {
        $reflectionClass = new ReflectionClass(Response::class);
        $constants = $reflectionClass->getConstants();

        foreach ($constants as $status => $value) {
            if ($value == $statusCode) {
                return $status;
            }
        }

        return null;
    }
}
