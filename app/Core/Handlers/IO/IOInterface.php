<?php
namespace App\Core\Handlers\IO;

/**
 * Interface for doing warehouse IO/API
 *
 * Interface IOInterface
 * @package App\Models\Warehouse\Handler
 * @todo Break this out into different interfaces
 *       A client should never be forced to implement an interface that it
 *       doesn't use, or clients shouldn’t be forced to depend on methods they
 *       do not use. (Interface Segregation Principle from SOLID principles)
 *       interface StreamInterface { public function start(); public function stop(...); }
 *       interface InputInterface extends StreamInterface { public function receive(...); }
 *       interface OutputInterface extends StreamInterface { public function send(...); }
 *       interface RollbackableInterface { public function rollback(...); }
 *       This way a class can implement either one, two, or all three of these
 *       interfaces depending on what methods it implements. The StreamInterface
 *       would not be directly implemented, but it would just be extended.
 */
interface IOInterface extends InputInterface, OutputInterface
{
    public const DATA_FIELD_ORDER = 'order';
    public const DATA_FIELD_ORDER_ITEMS = 'items';
    public const DATA_FIELD_ORDER_DROP = 'order_drop';
    public const DATA_FIELD_RAW_ORDER = 'raw_order';
    public const DATA_FIELD_WAREHOUSE_ORDER = 'warehouse_order';
}
