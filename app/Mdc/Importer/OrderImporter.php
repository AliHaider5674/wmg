<?php
namespace App\Mdc\Importer;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use Symfony\Component\Finder\Finder;

/**
 * Convert M1 Order files into MES files
 * This is use for testing stage only
 *
 * Class OrderConverter
 * @category WMG
 * @package  App\Mes\Converter
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderImporter
{
    public function import(string $dir, string $filePattern = 'D2C_MGNTO.ORD.*.xml')
    {
        $files = $this->getFiles($dir, $filePattern);
        foreach ($files as $file) {
            $this->importFile($file->getRealPath());
        }
    }

    private function importFile(string $file)
    {
        $orders = simplexml_load_file($file);
        $count = 0;
        foreach ($orders->Order as $order) {
            $count++;
            $orderData = [
                'sales_channel' => 'm1',
                'request_id' => basename($file) . '-' . $count,
                'order_id' => (string)$order->CustomerOrderNumber,
                'items' => []
            ];
            $orderModel = new Order();
            $orderModel->fill($orderData)->save();
            list($firstName, $lastName) = explode(' ', (string)$order->ShipInfo->ShipName);
            $addressData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address1' => (string)$order->ShipInfo->ShipAddress1,
                'address2' => (string)$order->ShipInfo->ShipAddress2,
                'city' => (string)$order->ShipInfo->ShipCity,
                'state' => (string)$order->ShipInfo->USShipState,
                'zip' => (string)$order->ShipInfo->ShipPostalCode,
                'country_code' => (string)$order->ShipInfo->ShipCountry,
            ];
            $addressModel = new OrderAddress();
            $addressModel->fill($addressData)
                ->setAttribute('parent_id', $orderModel->id)
                ->save();
            $orderTotal = (string)$order->OrderTotalDollarAmount;

            $orderLineNumber = 0;
            foreach ($order->OrderDetailLines as $orderLine) {
                $orderLineNumber++;
                $netAmount = (float)$orderLine->NetUnitPrice;
                $netAmount = $netAmount >0 ? $netAmount : $orderTotal/(float)$orderLine->Quantity;
                $itemData = [
                    'order_line_id' => (string)$orderLine->LineNumber,
                    'sku' => (string)$orderLine->UPC,
                    'name' => '',
                    'net_amount' => $netAmount,
                    'tax_amount' => '0',
                    'tax_rate' => '0',
                    'currency' => 'USD',
                    'quantity' => (string)$orderLine->Quantity,
                    'order_line_number' => $orderLineNumber,
                    'aggregated_line_id' => $orderLineNumber
                ];
                $orderItemModel = new OrderItem();
                $orderItemModel->fill($itemData)
                    ->setAttribute('parent_id', $orderModel->id)
                    ->setAttribute('source_id', 'US')
                    ->save();
            }
        }
    }

    private function getFiles(string $dir, string $filePattern)
    {
        $finder = new Finder();
        $finder->files()->depth('==0')->in($dir)
            ->name($filePattern);
        return $finder;
    }
}
