<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusQuadPayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var PaymentDescriptionProviderInterface */
    private $paymentDescriptionProvider;

    public function __construct(PaymentDescriptionProviderInterface $paymentDescriptionProvider)
    {
        $this->paymentDescriptionProvider = $paymentDescriptionProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        $details = [
            'amount' => $this->amountFormat($payment->getAmount(), $currency),
            'consumer' => $this->getConsumerData($order),
            'billing' => $this->getBillingData($order),
            'shipping' => $this->getShippingData($order),
            'description' => $this->paymentDescriptionProvider->getPaymentDescription($payment),
            'items' => $this->getItemsData($order, $currency),
            'merchantReference' => $order->getNumber(),
            'taxAmount' => $this->amountFormat($order->getTaxTotal(), $currency),
            'shippingAmount' => $this->amountFormat($order->getShippingTotal(), $currency),
        ];

        $request->setResult($details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
        ;
    }

    private function getConsumerData(OrderInterface $order): array
    {
        $customer = $order->getCustomer();
        $shippingAddress = $order->getShippingAddress();

        return [
            'phoneNumber' => $shippingAddress->getPhoneNumber(),
            'givenNames' => $shippingAddress->getFirstName(),
            'surname' => $shippingAddress->getLastName(),
            'email' => $customer->getEmail(),
        ];
    }

    private function getBillingData(OrderInterface $order): array
    {
        $billingAddress = $order->getBillingAddress();

        return [
            'addressLine1' => $billingAddress->getStreet(),
            'addressLine2' => '',
            'city' => $billingAddress->getCity(),
            'postcode' => $billingAddress->getPostcode() ?? '',
            'state' => $billingAddress->getProvinceCode() ?? '',
        ];
    }

    private function getShippingData(OrderInterface $order): array
    {
        $shippingAddress = $order->getShippingAddress();

        return [
            'addressLine1' => $shippingAddress->getStreet(),
            'addressLine2' => '',
            'city' => $shippingAddress->getCity(),
            'postcode' => $shippingAddress->getPostcode() ?? '',
            'state' => $shippingAddress->getProvinceCode() ?? '',
        ];
    }

    private function getItemsData(OrderInterface $order, $currency): array
    {
        $items = [];

        /** @var OrderItemInterface $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $product = $orderItem->getProduct();

            $items[] = [
                'description' => $product->getShortDescription(),
                'name' => $product->getName(),
                'sku' => $product->getCode(),
                'quantity' => $orderItem->getQuantity(),
                'price' => $this->amountFormat($orderItem->getUnitPrice(), $currency),
            ];
        }

        return $items;
    }

    public function amountFormat(int $amount, $currency): string
    {
        $divisor = 10 ** $currency->exp;

        return  number_format(abs($amount / $divisor), 2, '.', '');
    }
}
