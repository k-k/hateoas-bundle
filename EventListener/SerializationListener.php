<?php

namespace Kmfk\Bundle\HateoasBundle\EventListener;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\ObjectEvent;

use Symfony\Component\HttpFoundation\Request;

use Kmfk\Bundle\HateoasBundle\Annotation\Hateoas;

use Doctrine\Common\Annotations\Reader;

/**
 * Add Hateoas compliant resource urls based on Annotations
 * after serialization
 */
class SerializationListener implements EventSubscriberInterface
{
    /**
     * Links array to be included in serialization
     *
     * @var array
     */
    protected $links;

    /**
     * The API host, set in the configuration
     *
     * @var string
     */
    protected $host;

    /**
     * A Path Prefix for the Url, set in the configuration
     *
     * @var string
     */
    protected $prefix;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * Hateoas Annotation Class
     * @var string
     */
    protected $annotationClass = 'Kmfk\Bundle\HateoasBundle\Annotation\Hateoas';

    /**
     * Constructor
     */
    public function __construct(Request $request, Reader $reader, array $config = [])
    {
        $this->request  = $request;
        $this->reader   = $reader;

        foreach($config as $key => $value) {
            if (property_exists($this, $key) && !empty($value)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [ 
            [
                'event'  => 'serializer.post_serialize', 'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'onPostSerialize'
            ]
        ];
    }

    /**
     * @param ObjectEvent $event
     *
     * @return void
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        // Reset links array
        $this->links = [];

        // Get Exclusion Groups from the Serialization Context
        $attr = iterator_to_array( $event->getContext()->attributes);
        $groups = !empty($attr['groups']) ? $attr['groups'] : [];

        // Reflection Class of the Entity being Serialized
        $reflClass = new \ReflectionClass($event->getObject());

        // Custom Annotations not found in doctrine Proxy class - use the parent
        if ($reflClass->implementsInterface("Doctrine\Common\Persistence\Proxy")) {
            $reflClass = $reflClass->getParentClass();
        }

        // Get the Class Level Hateoas Annotation, if it exists
        $annotation = $this->reader->getClassAnnotation($reflClass, $this->annotationClass);
        if (null !== $annotation) {
            // Check to see if the hateoas groups match the exclusion groups
            if (empty($annotation->groups) || sizeof(array_intersect($annotation->groups, $groups))) {
                $this->addLinkUrl($annotation, $event->getObject());
            }
        }

        // Get the Property Level Hateoas Annotations, if they exist
        foreach($reflClass->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, $this->annotationClass);
            if (null === $annotation) {
                continue;
            }

            // Check to see if the hateoas groups match the exclusion groups
            if (empty($annotation->groups) || sizeof(array_intersect($annotation->groups, $groups))) {
                $this->addLinkUrl($annotation, $event->getObject());
            }
        }

        // Only return the _links property if necessary
        if (sizeof($this->links)) {
            $event->getVisitor()->addData('_links', $this->links);
        }
    }

    /**
     * Generates and appends the Resource Url to the Links array
     *
     * @return void
     */
    protected function addLinkUrl($annotation, $entity)
    {
        $uri =  $this->host ?: $this->request->getSchemeAndHost();
        $uri .= ltrim($this->prefix, '/') ?: null;

        $params = [];
        foreach($annotation->params as $key => $method) {
            if (method_exists($entity, $method)) {
                $params["{{$key}}"] = $entity->{$method}();
            }
        }
        $href = str_replace(array_keys($params), $params, $annotation->href);

        if ($annotation->type == "embedded") {
            if (!empty($this->links['self']['href'])) {
                $uri = $this->links['self']['href'];
            } else {
                $uri .= str_replace([$this->prefix, ltrim($href, '/')], '', $this->request->getPathInfo());
            }
        }

        $this->links[$annotation->name] = ['href' => $uri . ltrim($href, '/')];
    }
}
