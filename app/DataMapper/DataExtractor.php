<?php
namespace App\DataMapper;

use App\DataMapper\Exceptions\InvalidMappingException;
use App\DataMapper\Map\MapInterface;
use App\DataMapper\Parsers\SyntaxParser;
use Illuminate\Support\Arr;

/**
 * @todo enhance this to support more syntax and speed for extracting data
 */
class DataExtractor
{
    private Arr $arr;
    private SyntaxParser $syntaxParser;
    private array $extractors;
    public function __construct(
        Arr $arr,
        SyntaxParser $parser,
        array $extractors
    ) {
        $this->arr = $arr;
        $this->syntaxParser = $parser;
        $this->extractors = $extractors;
    }

    /**
     * @param array        $srcObj
     * @param MapInterface $map
     * @return array|array[]
     * @throws InvalidMappingException
     */
    public function extract(array $srcObj, MapInterface $map) : array
    {
        if ($this->isCollectionResult($map)) {
            return $this->extractCollection($srcObj, $map);
        }
        return $this->extractSingle($srcObj, $map);
    }

    /**
     * @param array        $srcObj
     * @param MapInterface $map
     * @return array
     */
    private function extractSingle(array $srcObj, MapInterface $map) : array
    {
        $data = [];
        foreach ($map->getMap() as $key => $path) {
            if ($key === $this->syntaxParser::TYPE_KEY) {
                continue;
            }
            $value = $this->getPathValue($path, $srcObj);
            $this->arr::set($data, $key, $value);
        }
        return $data;
    }

    /**
     * @param $path
     * @param $srcObj
     * @return array|\ArrayAccess|false|mixed
     */
    private function getPathValue($path, $srcObj)
    {
        if (is_callable($path)) {
            return call_user_func($path, $srcObj);
        }
        $callable = null;
        if (is_array($path)) {
            $callable = $path[1];
            $path = $path[0];
        }
        $path = str_replace('[]', '', $path);
        $names = array_keys($this->syntaxParser::REGEX);
        foreach ($names as $name) {
            $isName = 'is'.ucwords($name);
            $extractor = strtolower($name);
            if ($this->syntaxParser->{$isName}($path)) {
                $value = $this->extractors[$extractor]->getData($path, $srcObj);
                if ($callable) {
                    $value = call_user_func($callable, $value);
                }
                return $value;
            }
        }
        $value = $this->arr::get($srcObj, $path);
        if ($callable) {
            $value = call_user_func($callable, $value);
        }
        return $value;
    }

    /**
     * @param array        $srcObj
     * @param MapInterface $map
     * @return array
     * @throws InvalidMappingException
     */
    private function extractCollection(array $srcObj, MapInterface $map):array
    {
        $index = $this->getCollectionKeyPath($map);
        $collection = [];
        $dataCollection = $this->arr::get($srcObj, $index);
        foreach ($dataCollection as $item) {
            $this->arr::set($srcObj, $index, $item);
            $data = $this->extractSingle($srcObj, $map);
            $collection[] = $data;
        }
        return $collection;
    }

    private function isCollectionResult(MapInterface $map)
    {
        $mapDetail = $map->getMap();
        return isset($mapDetail['__type']) && $mapDetail['__type'] === 'collection';
    }

    /**
     * @param MapInterface $map
     * @return String
     * @throws InvalidMappingException
     */
    private function getCollectionKeyPath(MapInterface $map): String
    {
        $result = [];
        foreach ($map->getMap() as $path) {
            $matches = [];
            if (is_callable($path)) {
                continue;
            }
            if (is_array($path)) {
                $path = $path[0];
            }
            preg_match($this->syntaxParser->getRegex('loop'), $path, $matches);
            if (count($matches)>1) {
                $result[$matches[1]] = true;
            }
        }
        $keys = array_keys($result);
        if (count($keys) !== 1) {
            throw new InvalidMappingException('Unable to support multiple index.');
        }
        return $keys[0];
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $type = substr(strtolower($name), 3, strlen($name) - 3 - strlen('data'));
            preg_match($this->syntaxParser->getRegex($type), $arguments[0], $matches);
            return $matches[1];
        }
        return null;
    }
}
