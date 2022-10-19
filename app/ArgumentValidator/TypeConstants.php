<?php

namespace App\ArgumentValidator;

/**
 * Class TypeConstants
 * @package App\ArgumentValidator
 */
class TypeConstants
{
    /**  Boolean type */
    public const BOOLEAN = "boolean";

    /** Integer type */
    public const INTEGER = "integer";

    /** Float type */
    public const FLOAT = "double";

    /** String type */
    public const STRING = "string";

    /** Array type */
    public const ARRAY = "array";

    /** Object type */
    public const OBJECT = "object";

    /** Resource type */
    public const RESOURCE = "resource";

    /** Closed resource type */
    public const RESOURCE_CLOSED = "resource (closed)";

    /** Null type */
    public const NULL = "NULL";

    /** Unknown type */
    public const UNKNOWN = "unknown type";
}
