<?php

namespace App\MES\Console\Commands;

use Illuminate\Console\Command;
use FileDataConverter\File\Flat;
use FileDataConverter\File\Flat\Data\Schema;
use FileDataConverter\File\Flat\Data\FixLength;
use FileDataConverter\File\Flat\Data\SectionDetector;
use Symfony\Component\Console\Input\InputOption;

/**
 * Read MES files
 *
 * Class Fulfillment
 * @category WMG
 * @package  App\Console\Commands
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Reader extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:mes:read {file}';
    protected $readers;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read MES files, only support type: order, stock, ack, shipment';

    public function __construct()
    {
        parent::__construct();

        $this->addOption('type', 't', InputOption::VALUE_REQUIRED, 'File type');
        $this->readers = [];
        //Order
        $schemaFile = app_path('MES/Schema/order.yml');
        $schema = new Schema();
        $schema->loadSchema($schemaFile);
        $sectionDetector = new SectionDetector($schema);
        $fixLengthParser = new FixLength($schema, 'UTF-8', 'ISO-8859-1');
        $this->readers['order'] = new Flat($fixLengthParser, $sectionDetector);

        //Stock
        $schemaFile = app_path('MES/Schema/stock.yml');
        $schema = new Schema();
        $schema->loadSchema($schemaFile);
        $sectionDetector = new SectionDetector($schema);
        $fixLengthParser = new FixLength($schema, 'UTF-8', 'ISO-8859-1');
        $this->readers['stock'] = new Flat($fixLengthParser, $sectionDetector);

        //Shipment
        $schemaFile = app_path('MES/Schema/shipment.yml');
        $schema = new Schema();
        $schema->loadSchema($schemaFile);
        $sectionDetector = new SectionDetector($schema);
        $fixLengthParser = new FixLength($schema, 'UTF-8', 'ISO-8859-1');
        $this->readers['shipment'] = new Flat($fixLengthParser, $sectionDetector);

        //Ack
        $schemaFile = app_path('MES/Schema/ack.yml');
        $schema = new Schema();
        $schema->loadSchema($schemaFile);
        $sectionDetector = new SectionDetector($schema);
        $fixLengthParser = new FixLength($schema, 'UTF-8', 'ISO-8859-1');
        $this->readers['ack'] = new Flat($fixLengthParser, $sectionDetector);
    }

    /**
     * Drop orders
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $file = $this->argument('file');
        if (array_key_exists($this->option('type'), $this->readers)) {
            $this->readers[$this->option('type')]->read($file, array($this, 'outputData'));
            return;
        }
        $this->line('Unsupport file type.');
    }

    /**
     * output file data
     *
     * @param $data
     * @param $section
     *
     * @return $this
     */
    public function outputData($data, $section)
    {
        $this->line('----------------------------');
        $this->line($section . ':');
        $this->line('----------------------------');
        print_r($data);
        return $this;
    }
}
