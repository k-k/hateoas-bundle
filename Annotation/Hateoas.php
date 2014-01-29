<?php
/**
 * @author    Keith Kirk
 * @date      1/28/14
 */
namespace Kmfk\Bundle\HateoasBundle\Annotation;

/**
 * @Annotation
 */
class Hateoas
{
    /**
     * Links Type/Name
     *
     * @var string
     */
    public $name = 'self';

    /**
     * Entity or Collecitons Resource Url
     *
     * @var string
     */
    public $href;

    /**
     * Whether or not the Url should be Absolute or Embedded
     * Embedded Urls are appended to the `self` url
     *
     * @var string
     */
    public $type = 'absolute';

    /**
     * Array of property names and class methods used to fetch values
     *
     * @var array
     */
    public $params = [];

    /**
     * Exclusion Groups for Serializer
     *
     * @var array
     */
    public $groups = [];

    public function __construct(array $data)
    {
        // Validate the properties
        if (empty($data['name'])) {
            throw new \RuntimeException('A `name` attribute must be spcified!');
        }

        if (empty($data['href'])) {
            throw new \RuntimeException('An `href` attribute must be spcified!');
        }

        if (isset($data['type']) && !in_array($data['type'], ['absolute', 'embedded'])) {
            throw new \RuntimeException('The `type` must be either `absolute` or `embedded`');
        }

        if (!empty($data['params']) && !is_array($data['params'])) {
            throw new \RuntimeException('The `params` property must be an array');
        }

        if (!empty($data['groups']) && !is_array($data['groups'])) {
            throw new \RuntimeException('The `groups` property must be an array');
        }

        // Set the class properties
        foreach($data as $key => $value ) {
            if (property_exists($this, $key) && !empty($value)) {
                $this->{$key} = $value;
            }
        }
    }
}
