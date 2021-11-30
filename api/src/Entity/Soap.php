<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\SoapRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * This enity is used to transle incommong and outgoind soap calls
 *
 * @ApiResource()
 * @ORM\Entity(repositoryClass=SoapRepository::class)
 */
class Soap
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The internal name of this soap call
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * A short description of this soap call
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * The message type of this soap translation
     *
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * The entity form the EAV stack that this SOAP connection wants to use
     *
     * @ORM\ManyToOne(targetEntity=Entity::class, inversedBy="fromSoap")
     * @ORM\JoinColumn( referencedColumnName="id", nullable=true)
     */
    private $toEntity;

    /**
     * The entity form the EAV stack that this SOAP connection wants to use
     *
     * @ORM\OneToOne(targetEntity=Entity::class, inversedBy="toSoap")
     * @ORM\JoinColumn( referencedColumnName="id", nullable=true)
     */
    private $fromEntity;

    /**
     * An XML descriping the request that we want to recieve
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $request;

    /**
     * An array build of request that we want to recieve
     *
     * @ORM\Column(type="array")
     */
    private $requestSkeleton = [];

    /**
     * An array containing an request to entity translation in dot notation e.g. contact.firstname => person.name
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private $requestHydration = [];

    /**
     * An XML descriping the response that we want t0 send
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $response;

    /**
     * An array build of response that  we want to send
     *
     * @ORM\Column(type="array")
     */
    private $responseSkeleton = [];

    /**
     * An array containing an entity to response transaltion in dot notation e.g. person.name => contact.firstname
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private $responseHydration = [];

    /**
     * A string to define the caseType of StUF Lk01 messages
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zaaktype;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getToEntity(): ?Entity
    {
        return $this->toEntity;
    }

    public function setToEntity(?Entity $toEntity): self
    {
        $this->toEntity = $toEntity;

        return $this;
    }

    public function getFromEntity(): ?Entity
    {
        return $this->fromEntity;
    }

    public function setFrom(?Entity $fromEntity): self
    {
        $this->fromEntity = $fromEntity;

        return $this;
    }

    public function getRequestSkeleton(): ?array
    {
        return $this->requestSkeleton;
    }

    public function setRequestSkeleton(array $requestSkeleton): self
    {
        $this->requestSkeleton = $requestSkeleton;

        return $this;
    }

    public function getResponseSkeleton(): ?array
    {
        return $this->responseSkeleton;
    }

    public function setResponseSkeleton(array $responseSkeleton): self
    {
        $this->responseSkeleton = $responseSkeleton;

        return $this;
    }

    public function getRequestHydration(): ?array
    {
        return $this->requestHydration;
    }

    public function setRequestHydration(?array $requestHydration): self
    {
        $this->requestHydration = $requestHydration;

        return $this;
    }

    public function getResponseHydration(): ?array
    {
        return $this->responseHydration;
    }

    public function setResponseHydration(?array $responseHydration): self
    {
        $this->responseHydration = $responseHydration;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(string $response): self
    {
        $this->response = $response;

        // Lets use this template to generate a skeleton
        $xmlEncoder = new XmlEncoder(['xml_root_node_name' => 'soap:Envelope']);
        $this->responseSkeleton = $xmlEncoder->decode($response, 'xml');

        return $this;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setRequest(string $request): self
    {
        $this->request = $request;

        // Lets use this template to generate a skeleton
        $xmlEncoder = new XmlEncoder(['xml_root_node_name' => 'soap:Envelope']);
        $this->requestSkeleton = $xmlEncoder->decode($request, 'xml');

        return $this;
    }

    public function getZaaktype(): ?string
    {
        return $this->zaaktype;
    }

    public function setZaaktype(?string $zaaktype): self
    {
        $this->zaaktype = $zaaktype;

        return $this;
    }
}