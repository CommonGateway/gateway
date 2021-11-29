<?php

namespace App\Controller;

use App\Service\SOAPService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * @Route("/soap")
 */
class SOAPController extends AbstractController
{
    /**
     * @Route("/stuf", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function soapAction(Request $request, SOAPService $SOAPService, EntityManagerInterface $em): Response
    {
        $xmlEncoder = new XmlEncoder(['xml_root_node_name' => 'soap:Envelope']);
        $soapAction = $request->headers->get('SOAPAction');
        $data = $xmlEncoder->decode($request->getContent(), 'xml');
        $namespaces = $SOAPService->getNamespaces($data);
        $messageType = $SOAPService->getMessageType($data, $namespaces);


        switch ($messageType) {
            case 'zakLv01':
                $message = $SOAPService->processZakLv01Message($data, $namespaces);
                break;
            case 'edcLv01':
                $message = $SOAPService->processEdcLv01Message($data, $namespaces);
                break;
            case 'edcLk01':
                $message = $SOAPService->processEdcLk01($data, $namespaces, $request);
                break;
            case 'genereerDocumentIdentificatie_Di02':
                $message = $SOAPService->processDi02($data, $namespaces, 'genereerDocumentidentificatie', $request);
                break;
            case 'genereerZaakIdentificatie_Di02':
                $message = $SOAPService->processDi02($data, $namespaces, 'genereerZaakIdentificatie', $request);
                break;
            default:
                if($soapEntity = $this->getDoctrine()->getRepository('App:Soap')->findOneBy(['type'=>$messageType])){
                    $message = $SOAPService->handleRequest($soapEntity, $data, $namespaces, $request);
                }
                else{
                    throw new BadRequestException("The message type $messageType is not supported at this moment");
                }
        }

        return new Response($message, 200, [
            'Content-Type'                     => 'application/soap+xml',
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'GET,POST,PUT,DELETE,PATCH,OPTIONS',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers'     => 'Content-Type',
            'Strict-Transport-Security'        => 'max-age=15724800; includeSubDomains',
        ]);
    }
}
