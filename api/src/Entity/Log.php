<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use App\Repository\LogRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=LogRepository::class)
 */
class Log
{
    /**
     * @var UuidInterface The UUID identifier of this Entity.
     *
     * @Groups({"read"})
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var string The type of this Log.
     *
     * @Assert\NotNull
     * @Assert\Length(
     *      max = 255
     * )
     * @Assert\Choice({"in", "out"})
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *             "enum"={"in", "out"},
     *             "example"="in"
     *         }
     *     }
     * )
     * @Groups({"read","read_secure","write"})
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @var ?string The request method of this Log.
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $requestMethod;

    /**
     * @var ?array The request headers of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $requestHeaders = [];

    /**
     * @var ?array The request query of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $requestQuery = [];

    /**
     * @var ?string The request path info of this Log.
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $requestPathInfo;

    /**
     * @var ?string The request languages of this Log.
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $requestLanguages;

    /**
     * @var ?array The request server of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $requestServer = [];

    /**
     * @var ?string The request context for this Log.
     *
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *             "example"="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c"
     *         }
     *     }
     * )
     * @Groups({"read","write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $requestContext;

    /**
     * @var ?string The response status of this Log.
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $responseStatus;

    /**
     * @var ?int The response status code of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $responseStatusCode;

    /**
     * @var ?array The response headers of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $responseHeaders = [];

    /**
     * @var ?array The response content of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $responseContent = [];

    /**
     * @var ?string The session of this Log.
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $session;

    /**
     * @var ?array The session values of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $sessionValues = [];

    /**
     * @var ?object The endpoint of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="object", nullable=true)
     */
    private $endpoint;

    /**
     * @var ?object The handler of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="object", nullable=true)
     */
    private $handler;

    /**
     * @var ?object The entity of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="object", nullable=true)
     */
    private $entity;

    /**
     * @var ?object The source of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="object", nullable=true)
     */
    private $source;

    /**
     * @var ?DateTime The endpoint of this Log.
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="time")
     */
    private $responseTime;

    /**
     * @var Datetime The moment this request was created
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(?string $requestMethod): self
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    public function getRequestHeaders(): ?array
    {
        return $this->requestHeaders;
    }

    public function setRequestHeaders(?array $requestHeaders): self
    {
        $this->requestHeaders = $requestHeaders;

        return $this;
    }

    public function getRequestQuery(): ?array
    {
        return $this->requestQuery;
    }

    public function setRequestQuery(?array $requestQuery): self
    {
        $this->requestQuery = $requestQuery;

        return $this;
    }

    public function getRequestPathInfo(): ?string
    {
        return $this->requestPathInfo;
    }

    public function setRequestPathInfo(?string $requestPathInfo): self
    {
        $this->requestPathInfo = $requestPathInfo;

        return $this;
    }

    public function getRequestLanguages(): ?string
    {
        return $this->requestLanguages;
    }

    public function setRequestLanguages(?string $requestLanguages): self
    {
        $this->requestLanguages = $requestLanguages;

        return $this;
    }

    public function getRequestServer(): ?array
    {
        return $this->requestServer;
    }

    public function setRequestServer(?array $requestServer): self
    {
        $this->requestServer = $requestServer;

        return $this;
    }

    public function getRequestContext(): ?string
    {
        return $this->requestContext;
    }

    public function setRequestContext(?string $requestContext): self
    {
        $this->requestContext = $requestContext;

        return $this;
    }

    public function getResponseStatus(): ?string
    {
        return $this->responseStatus;
    }

    public function setResponseStatus(?string $responseStatus): self
    {
        $this->responseStatus = $responseStatus;

        return $this;
    }

    public function getResponseStatusCode(): ?int
    {
        return $this->responseStatusCode;
    }

    public function setResponseStatusCode(?int $responseStatusCode): self
    {
        $this->responseStatusCode = $responseStatusCode;

        return $this;
    }

    public function getResponseHeaders(): ?array
    {
        return $this->responseHeaders;
    }

    public function setResponseHeaders(?array $responseHeaders): self
    {
        $this->responseHeaders = $responseHeaders;

        return $this;
    }

    public function getResponseContent(): ?array
    {
        return $this->responseContent;
    }

    public function setResponseContent(?array $responseContent): self
    {
        $this->responseContent = $responseContent;

        return $this;
    }

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(?string $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getSessionValues(): ?array
    {
        return $this->sessionValues;
    }

    public function setSessionValues(?array $sessionValues): self
    {
        $this->sessionValues = $sessionValues;

        return $this;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setEndpoint($endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setHandler($handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getResponseTime(): ?\DateTimeInterface
    {
        return $this->responseTime;
    }

    public function setResponseTime(\DateTimeInterface $responseTime): self
    {
        $this->responseTime = $responseTime;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
