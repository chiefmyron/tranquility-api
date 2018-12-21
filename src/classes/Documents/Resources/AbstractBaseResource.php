<?php namespace Tranquility\Documents\Resources;

// Tranquility classes
use Tranquility\Resources\Transformers\AbstractTransformer;

abstract class AbstractBaseResource {

    /**
     * The entity or data to be represented in the document
     *
     * @var mixed
     */
    protected $data;

    /**
     * Relevant metadata to display in the document
     *
     * @var array
     */
    protected $meta = [];

    /**
     * Collection of links to the relating to the document
     *
     * @var array
     */
    protected $links = [];

    /**
     * The JSON:API schema version that the document conforms to
     *
     * @var string
     */
    protected $jsonApiVersion = "1.0";

    /**
     * A callable to process the data attached to this resource.
     *
     * @var callable|AbstractTransformer|null
     */
    protected $transformer;

    /**
     * The resource key.
     *
     * @var string
     */
    protected $resourceKey;

    /**
     * Create a new resource instance.
     *
     * @param mixed                             $data
     * @param callable|AbstractTransformer|null $transformer
     * @param string                            $resourceKey
     */
    public function __construct($data = null, $transformer = null, $resourceKey = null) {
        $this->data = $data;
        $this->transformer = $transformer;
        $this->resourceKey = $resourceKey;
    }

    /**
     * Get the JSON:API version that the document conforms to
     *
     * @return string
     */
    public function getJsonApiVersion() {
        return $this->jsonApiVersion;
    }

    /**
     * Get the data.
     *
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Set the data.
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data) {
         $this->data = $data;
         return $this;
    }

    /**
     * Get the meta data.
     *
     * @return array
     */
    public function getMeta() {
        return $this->meta;
    }

    /**
     * Set the meta data.
     *
     * @param array $meta
     *
     * @return $this
     */
    public function setMeta(array $meta) {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Get the meta data.
     *
     * @param string $key
     *
     * @return array
     */
    public function getMetaValue($key) {
        return $this->meta[$key];
    }

    /**
     * Set the meta data.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setMetaValue($key, $value) {
        $this->meta[$key] = $value;
        return $this;
    }

    /**
     * Get the link collection.
     *
     * @return array
     */
    public function getLinks() {
        return $this->links;
    }

    /**
     * Set the link collection
     *
     * @param array $links
     *
     * @return $this
     */
    public function setLinks(array $links) {
        $this->links = $link;
        return $this;
    }

    /**
     * Get the resource key.
     *
     * @return string
     */
    public function getResourceKey() {
        return $this->resourceKey;
    }

    /**
     * Set the resource key.
     *
     * @param string $resourceKey
     *
     * @return $this
     */
    public function setResourceKey($resourceKey) {
        $this->resourceKey = $resourceKey;
        return $this;
    }

    /**
     * Get the transformer.
     *
     * @return callable|TransformerAbstract
     */
    public function getTransformer() {
        return $this->transformer;
    }

    /**
     * Set the transformer.
     *
     * @param callable|TransformerAbstract $transformer
     *
     * @return $this
     */
    public function setTransformer($transformer) {
        $this->transformer = $transformer;
        return $this;
    }
}