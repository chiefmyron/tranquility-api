<?php namespace Tranquility\Resources;

use ArrayIterator;

abstract class AbstractResourceCollection extends AbstractResource {
    /**
     * A collection of data.
     *
     * @var array|ArrayIterator
     */
    protected $data;

    /**
     * The paginator instance.
     *
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * The cursor instance.
     *
     * @var CursorInterface
     */
    protected $cursor;

    /**
     * Get the paginator instance.
     *
     * @return PaginatorInterface
     */
    public function getPaginator() {
        return $this->paginator;
    }

    /**
     * Determine if the resource has a paginator implementation.
     *
     * @return bool
     */
    public function hasPaginator() {
        return $this->paginator instanceof PaginatorInterface;
    }

    /**
     * Get the cursor instance.
     *
     * @return CursorInterface
     */
    public function getCursor() {
        return $this->cursor;
    }

    /**
     * Determine if the resource has a cursor implementation.
     *
     * @return bool
     */
    public function hasCursor() {
        return $this->cursor instanceof CursorInterface;
    }

    /**
     * Set the paginator instance.
     *
     * @param PaginatorInterface $paginator
     *
     * @return $this
     */
    public function setPaginator(PaginatorInterface $paginator) {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Set the cursor instance.
     *
     * @param CursorInterface $cursor
     *
     * @return $this
     */
    public function setCursor(CursorInterface $cursor) {
        $this->cursor = $cursor;

        return $this;
    }
}