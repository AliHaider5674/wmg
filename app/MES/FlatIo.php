<?php
namespace App\MES;

use FileDataConverter\File\Flat;
use FileDataConverter\File\Flat\Data\Schema;
use FileDataConverter\File\Flat\Data\FixLength;
use FileDataConverter\File\Flat\Data\SectionDetector;

/**
 * Flat IO that factor different flat io types by a given
 * schema file
 *
 * Class FlatIo
 * @category WMG
 * @package  App\MES
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FlatIo
{
    /**
     * Build flat file io
     * @param $schemaFile
     * @return \FileDataConverter\File\Flat
     */
    public static function factoryFlatIo($schemaFile)
    {
        $schema = new Schema();
        $schema->loadSchema($schemaFile);
        $sectionDetector = new SectionDetector($schema);
        $fixLengthParser = new FixLength($schema, 'UTF-8', 'ISO-8859-1');
        $shipmentIo = new Flat($fixLengthParser, $sectionDetector);
        return $shipmentIo;
    }
}
