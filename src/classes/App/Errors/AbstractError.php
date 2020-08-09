<?php namespace Tranquility\App\Errors;

use Tranquility\System\Utility;

abstract class AbstractError {

    /**
     * A unique identifier for this particular occurrence of the problem.
     *
     * @var string
     */
    protected $id;

    /**
     * A collection of links relating to the error.
     *
     * @var array
     */
    protected $links;

    /**
     * The HTTP status code applicable to this problem, expressed as a string value.
     *
     * @var string
     */
    protected $codeHttpStatus;

    /**
     * An application-specific error code, expressed as a string value.
     *
     * @var string
     */
    protected $codeApplication;

    /**
     * A short, human-readable summary of the problem that SHOULD NOT change from occurrence to occurrence of the problem, except for purposes of localization.
     *
     * @var string
     */
    protected $title;

    /**
     * A human-readable explanation specific to this occurrence of the problem. Like title, this field’s value can be localized.
     *
     * @var string
     */
    protected $detail;

    /**
     * An object containing references to the source of the error. Can contain a 'pointer' and/or a 'parameter' member.
     *
     * @var array
     */
    protected $sources;

    /**
     * A meta object containing non-standard meta-information about the error.
     *
     * @var array
     */
    protected $meta;

    /**
     * Exception thrown if an unexpected query parameter has been provided and cannot be handled.
     *
     * @param string      $errorCode    Application error code
     * @param string      $description  [Optional] Publicly visible error message
     * @param string      $id           [Optional] Unique identifier for this occurance of the error. If not provided, a unique ID will be generated automatically.
     * @return Tranquility\Services\Errors\AbstractError
     */
    public function __construct(string $errorCode, string $description = null, string $id = null) {
        if (is_null($id)) {
            $id = Utility::generateUuid(1);
        }

        $this->id = $id;
        $this->codeApplication = $errorCode;
        $this->detail = $description;
        $this->links = [];
        $this->sources = [];
        $this->meta = [];
        return $this;
    }

    /**
     * Add a link relating to the error. Allowed link types are:
     *   - 'about': A link that leads to further details about this particular occurrence of the problem.
     *
     * @param string $linkType     Type of link being added. Valid values are: 'about'
     * @param string $linkAddress  URL for the link
     * @param array  $meta         [Optional] Additional information relating to the link
     * @return Tranquility\Services\Errors\AbstractError
     */
    public function addLink(string $linkType, string $linkAddress, array $meta = null) {
        // Validate link type is allowed
        $validLinkTypes = ['about'];
        if (in_array($linkType, $validLinkTypes) == false) {
            throw new \Exception("Link type '".$linkType."' cannot be added to a service error.");
        }

        // Add or replace link in collection
        $link = ['href' => $linkAddress];
        if (is_null($meta) == false) {
            $link['meta'] = $meta;
        }
        $this->links[$linkType] = $link;
        return $this;
    }

    /**
     * Add an error source related to the error. Allowed error source types are:
     *   - 'pointer': A JSON Pointer [RFC6901] to the associated entity in the request document (e.g. "/data" for a primary data object, or "/data/attributes/title" for a specific attribute).
     *   - 'parameter': A string indicating which URI query parameter caused the error.
     *
     * @param string $sourceType
     * @param string $sourceValue
     * @return Tranquility\Services\Errors\AbstractError
     */
    public function addErrorSource(string $sourceType, string $sourceValue) {
        // Validate source type is allowed
        $validSourceTypes = ['pointer', 'parameter'];
        if (in_array($sourceType, $validSourceTypes) == false) {
            throw new \Exception("Source type '".$sourceType."' cannot be added to a service error.");
        }

        // Add or replace error source in collection
        $this->sources[$sourceType] = $sourceValue;
        return $this;
    }

    /**
     * Add an array containing non-standard meta-information about the error.
     *
     * @param array $meta
     * @return Tranquility\Services\Errors\AbstractError
     */
    public function addMetaDetails(array $meta) {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Return an array representation of the error, conforming to the JSON:API v1 spec.
     *
     * @link https://jsonapi.org/format/#error-objects
     * @return array
     */
    public function toArray() {
        $result = [
            'id' => $this->id,
            'status' => $this->codeHttpStatus,
            'code' => $this->codeApplication,
            'title' => $this->title,
        ];

        // Add optional elements to result array
        if (is_null($this->detail) == false) {
            $result['detail'] = $this->detail;
        }
        if (count($this->links) > 0) {
            $result['links'] = $this->links;
        }
        if (count($this->sources) > 0) {
            $result['source'] = $this->sources;
        }
        if (count($this->meta) > 0) {
            $result['meta'] = $this->meta;
        }

        return $result;
    }
}