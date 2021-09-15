<?php

namespace App\Service;

use App\Entity\Attribute;
use App\Entity\Entity;
use App\Entity\ObjectEntity;
use App\Entity\Value;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;


class EavDocumentationService
{
    private EntityManagerInterface $em;
    private CommonGroundService $commonGroundService;
    private ValidationService $validationService;
    private array $supportedValidators;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ValidationService $validationService)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
        $this->validationService = $validationService;

        // Lets define the validator that we support for docummentation right now
        $this->supportedValidators = [
            'multipleOf',
            'maximum',
            'exclusiveMaximum',
            'minimum',
            'exclusiveMinimum',
            'maxLength',
            'minLength',
            'maxItems',
            'uniqueItems',
            'maxProperties',
            'minProperties',
            'required',
            'enum',
            'allOf',
            'oneOf',
            'anyOf',
            'not',
            'items',
            'additionalProperties',
            'default',
        ];
    }


    /**
     * Places an schema.yaml and schema.json in the /public/eav folder for use by redoc and swagger
     *
     * @return boolean returns true if succcesfull or false on failure
     */
    public function write(): array
    {
        // Get the docs
        $doc = $this->getRenderDocumentation();

        // Setup the file system
        $filesystem = new Filesystem();

        // Check if there is a eav folder in the /public folder
        if(!$filesystem->exists('/var/www/app/public/eav')){
            $filesystem->mkdir('/var/www/app/public/eav', 0700);
        }

        $filesystem->dumpFile('schema.json', $doc);
        $filesystem->dumpFile('schema.yaml', $doc);

        return true;

    }

    /**
     * Generates an OAS3 documentation for the exposed eav entities in the form of an array
     *
     * @return array
     */
    public function getRenderDocumentation(): array
    {
        $docs = [];

        // General info
        $docs['openapi'] = '3.0.3';
        $docs['info'] = [
            "title"=>"Commonground Gateway EAV endpoints", /*@todo pull from config */
            "description"=>"This documentation contains the EAV endpoints on your commonground gateway.",  /*@todo pull from config */
            "termsOfService"=>"http://example.com/terms/",  /*@todo pull from config */
            "contact"=> [
                "name"=> "Gateway Support", /*@todo pull from config */
                "url"=> "http://www.conduction.nl/contact", /*@todo pull from config */
                "email"=> "info@conduction.nl" /*@todo pull from config */
            ],
            "license"=> [
                "name"=> "Apache 2.0", /*@todo pull from config */
                "url"=> "https://www.apache.org/licenses/LICENSE-2.0.html" /*@todo pull from config */
            ],
          "version"=>"1.0.1"
        ];
        $docs['servers']=[
            ["url"=>"/api/eav/data","description"=>"Gateway server"]
        ];

        // General reusable components for the documentation
        $docs['components']=[
            "schemas"=>[
                "ErrorModel",
                "DeleteModel"
            ],
            "responces"=>[
                "error"=>[
                    "description"=>"error payload",
                    "content"=>[
                        "application/json" => [
                            "schema"=>[
                                "\$ref"=>'#/components/schemas/ErrorModel'
                            ]
                        ]
                    ]
                ]
            ],
            "parameters"=>[
                "ID" => [
                    "name"=>"id",
                    "in"=>"path",
                    "description"=>"ID of the object that you want to target",
                    "required"=>true,
                    "style"=>"simple"
                ],
                "Page" => [
                    "name"=>"page",
                    "in"=>"path",
                    "description"=>"The page of the  list that you want to use",
                    "style"=>"simple"
                ],
                "Limit" => [
                    "name"=>"limit",
                    "in"=>"path",
                    "description"=>"The total amount of items that you want to include in a list",
                    "style"=>"simple"
                ],
                "Start" => [
                    "name"=>"start",
                    "in"=>"path",
                    "description"=>"The firts item that you want returned, is used to determine the list offset. E.g. if you start at 50 the first 49 items wil not be returned",
                    "style"=>"simple"
                ]
            ]
        ];

        $entities = $this->em->getRepository()->findBy(['expose_in_docs'=>true]);

        foreach($entities as $entity){
            $docs = $this->addEntityToDocs($entity, $docs);
        }


        return $docs;
    }

    /**
     * Generates an OAS3 documentation for the exposed eav entities in the form of an array
     *
     * @param Entity $entity
     * @param array $docs
     * @return array
     */
    public function addEntityToDocs(Entity $entity, array $docs): array
    {

        $docs['paths']['/'.$entity->getPath()] = $this->getCollectionPaths($entity);
        $docs['paths']['/'.$entity->getPath().'/{id}'] = $this->getItemPaths($entity);

        /* @todo this only goes one deep */
        $docs['components']['schemas'][$entity->getName()] = $this->getItemSchema($entity);

        // create the tag
        $docs['tags'] = [
            "name"=>$entity->getName(),
	        "description">$entity->getDescription()
        ];

        return $docs;
    }

    /**
     * Generates an OAS3 documentation for the colleection paths of an entity
     *
     * @param Entity $entity
     * @return array
     */
    public function getCollectionPaths(Entity $entity): array
    {
        $docs = [
            "get" => [],
            "post" => [],
        ];

        return $docs;
    }


    /**
     * Generates an OAS3 documentation for the item paths of an entity
     *
     * @param Entity $entity
     * @return array
     */
    public function getItemPaths(Entity $entity): array
    {
        $docs = [];
        $types = ['get','put','delete'];
        foreach ($types as $type){

            // Basic path operations
            $docs[$type] = [
                "description"=>"Returns pets based on ID",
                "summary"=>"Find pets by ID",
                "operationId"=>"getPetsById",
                "responses"=>[
                    "default"=>[
                        "description"=>"error payload",
                        "content"=>[
                            "application/json" => [
                                "schema"=>[
                                    "\$ref"=>'#/components/schemas/ErrorModel'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // Type specific path responces
            switch ($type) {
                case 'put':
                    $docs[$type]["responses"]["200"] = [
                        "description"=>"put responce",
                        "content"=>[
                            "application/json" => [
                                "schema"=>[
                                    "\$ref"=>'#/components/schemas/ErrorModel' /*@ todo generate model */
                                ]
                            ]
                        ]
                    ];
                case 'delete':
                    $docs[$type]["responses"]["200"] = [
                        "description"=>"delete responce",
                        "content"=>[
                            "application/json" => [
                                "schema"=>[
                                    "\$ref"=>'#/components/schemas/DeleteModel'
                                ]
                            ]
                        ]
                    ];
            }
        }

        // Pat parameters
        $docs['parameters'] = [
            // Each parameter is a loose array
            "\$ref"=>'#/components/parameters/ID'

        ];

        return $docs;
    }

    /**
     * Generates an OAS3 schema for the item  of an entity
     *
     * @param Entity $entity
     * @return array
     */
    public function getSchema(Entity $entity): array
    {
        $schema = [
            "type"=>"object",
            "required"=>[],
            "properties"=>[],

        ];

        foreach($entity->getAttributes() as $attribute){

            // Handle requireded fields
            if($attribute->getRequired()){
                $schema['required'][] = $attribute->getName();
            }

            // Add the atribute
            $schema['properties'][$attribute->getName()] = [
                "type"=>$attribute->getType(),
                "title"=>$attribute->getName(),
                "description"=>$attribute->getDescription(),
            ];

            // The attribute might be a scheme on its own
            if($attribute->getObject()){
                $schema['properties'][$attribute->getName()] = ["\$ref"=>"#/components/schemas/".$attribute->getObject()->getObject()->getName()];
                // that also means that we don't have to do the rest
                continue;
            }

            /* @todo we nee to add supoort for https://swagger.io/specification/#schema-object
             *
             *
             */

            /* @todo ow nooz a loopin a loop */
            foreach($attribute->getValidation() as $validator => $validation){
                if(!array_key_exists($validator, $this->supportedValidators)){
                    $schema['properties'][$attribute->getName()][$validator] = $validation;
                }
            }
        }

        return $schema;
    }
}
