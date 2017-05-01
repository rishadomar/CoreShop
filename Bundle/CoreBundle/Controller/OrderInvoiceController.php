<?php

namespace CoreShop\Bundle\CoreBundle\Controller;

use CoreShop\Bundle\ResourceBundle\Controller\AdminController;
use CoreShop\Component\Order\Model\OrderInterface;
use CoreShop\Component\Order\Model\OrderItemInterface;
use CoreShop\Component\Order\Processable\ProcessableInterface;
use CoreShop\Component\Product\Model\ProductInterface;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use CoreShop\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo: maybe we should move this one to the AdminBundle?
 */
class OrderInvoiceController extends AdminController
{
    public function getInvoiceAbleItemsAction(Request $request)
    {
        $orderId = $request->get('id');
        $order = $this->getOrderRepository()->find($orderId);

        if (!$order instanceof OrderInterface) {
            return $this->json(['success' => false, 'message' => 'Order with ID "'.$orderId.'" not found']);
        }

        $items = [];
        $itemsToReturn = [];

        if (count($order->getPayments()) === 0) {
            return $this->json(['success' => false, 'message' => 'Can\'t create Invoice without valid order payment']);
        }

        try {
            $items = $this->getProcessableHelper()->getProcessableItems($order);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        foreach ($items as $item) {
            $orderItem = $item['item'];
            if ($orderItem instanceof OrderItemInterface) {
                $itemsToReturn[] = [
                    "orderItemId" => $orderItem->getId(),
                    "price" => $orderItem->getItemPrice(),
                    "maxToInvoice" => $item['quantity'],
                    "quantity" => $orderItem->getQuantity(),
                    "quantityInvoiced" => $orderItem->getQuantity() - $item['quantity'],
                    "toInvoice" => $item['quantity'],
                    "tax" => $orderItem->getTotalTax(),
                    "total" => $orderItem->getTotal(),
                    "name" => $orderItem->getProduct() instanceof ProductInterface ? $orderItem->getProduct()->getName() : ""
                ];
            }
        }

        return $this->json(['success' => true, 'items' => $itemsToReturn]);
    }

    /**
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function createInvoiceAction(Request $request) {
        $items = $request->get("items");
        $orderId = $request->get("id");
        $order = $this->getOrderRepository()->find($orderId);

        if (!$order instanceof OrderInterface) {
            return $this->json(['success' => false, 'message' => "Order with ID '$orderId' not found"]);
        }

        try {
            $items = $this->decodeJson($items);

            $invoice = $this->getInvoiceFactory()->createNew();
            $invoice = $this->getOrderToInvoiceTransformer()->transform($order, $invoice, $items);

            return $this->json(["success" => true, "invoiceId" => $invoice->getId()]);
        } catch (\Exception $ex) {
            return $this->json(['success' => false, 'message' => $ex->getMessage()]);
        }
    }

    /**
     * @return ProcessableInterface
     */
    private function getProcessableHelper() {
        return $this->get('coreshop.order.invoice.processable');
    }

    /**
     * @return PimcoreRepositoryInterface
     */
    private function getOrderRepository() {
        return $this->get('coreshop.repository.order');
    }

    private function getInvoiceFactory() {
        return $this->get('coreshop.factory.order_invoice');
    }

    private function getOrderToInvoiceTransformer() {
        return $this->get('coreshop.order.transformer.order_to_invoice');
    }
}