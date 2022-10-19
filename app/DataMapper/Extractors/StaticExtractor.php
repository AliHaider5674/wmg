<?php
namespace App\DataMapper\Extractors;

use App\DataMapper\Parsers\SyntaxParser;

/**
 * @class StaticExtractor
 */
class StaticExtractor implements ExtractorInterface
{
    private SyntaxParser $syntaxParser;
    public function __construct(SyntaxParser $syntaxParser)
    {
        $this->syntaxParser = $syntaxParser;
    }

    /**
     * @param $path
     * @param $dataSet
     * @return false|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($path, $dataSet)
    {
        preg_match($this->syntaxParser->getRegex('static'), $path, $matches);
        return $matches[1];
    }
}
