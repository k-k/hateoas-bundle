Hateoas Bundle
=======================

###Overview

The Hateoas Bundle works with JMS Serializer to allow you to easily add Hateoas compliant
resource urls to the JSON output of your REST Api.

There are other similar bundles, but they seemed heavy for my needs. This bundle was designed to work seamlessly with [JMS Serializer](https://github.com/schmittjoh/JMSSerializerBundle), with out needing to abstract or obsfucate the serialization of your data.

Currently this bundle only provides Annotations for Resource Linking in your Serialized response.

###Hateoas Bundle Installation

The best method of installation is through the use of composer.

#####Add the bundle to Composer

```json
"require": {
    "kmfk/hateoas-bundle": "~0.1",
}
```

#####Update AppKernel.php

Add The Hateoas Bundle to your kernel bootstrap sequence

```php
public function registerBundles()
{
	$bundles = array(
    	// ...
    	new Kmfk\Bundle\HateoasBundle\HateoasBundle(),
    );

    return $bundles;
}
```

####Configure the Bundle

The bundle allows you to configure the Rest API host and a possibly path prefix.
Your links will be built using these values.  If they are not set, the bundle will
default to parsing this from the current request.

```
#app/config.yml

hateoas:
	host:   http://api.example.com/
	prefix: /api/
```

###Annotations

Once configured, you can use the Annotations provided by this bundle to easily
add resource links to your classes and properties.

```php
#src/AcmeBundle/Entity/UserEntity.php

use Kmfk/Bundle/HateoasBundle/Annotation/Hateoas;

/**
 * @Hateoas(
 *      name    ="self",
 *      href    ="/user/{id}/"
 *      params  ={"id" = "getId"},
 *      groups  ={"list"},
 *      type    ="absolute"
 *  )
 */
class UserEntity
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}
```
####Output:

```json
"user": {
    "id": 1,
    "name": "Keith Kirk"
    },
    "_links": {
        "self": {
            "href": "http://api.example.com/api/user/1/"
        }
    }
}
```

###Annotation Reference

Property | Description | Example
-------- | ----------- | -------
`name` | The property name inside the 'links' attribute | 'user'
`href` | The relative (path) url of the resource, including url tokens | '/user/{id}/'
`params` | An associative array of token names with their corresponding getter methods | '{ "id" = "getId" }'
`groups` | Serializer Exclusion Groups | Used the same way as JMS Serializer Groups | '{ "partial", "full" }'
`type` | 'Absolute' or 'Embedded' | 'absolute'

####Using Params
You can have multiple tokens in the `href`.  The `params` array should be an associative array
with keys matching the tokens in the path.  Methods listed should be methods that exist in the 
annotated class.

####Groups
Specifying `groups` allow you to control the output of the links based on 
[Exclusion Groups](http://jmsyst.com/libs/serializer/master/reference/annotations#groups)

####Embedded vs Absolute Links
While `absolute` (default value), will allows include the API Host and optional prefix, 
`embedded` urls live beneath another resource. Setting type to '`embedded` will allow you 
to have links like:

```json
"_links": {
    "self": {
        "href": "http://api.example.com/api/user/1/email/1/"
    }
}
```
