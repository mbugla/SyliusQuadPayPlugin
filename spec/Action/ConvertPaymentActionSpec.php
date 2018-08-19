<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusQuadPayPlugin\Action;

use BitBag\SyliusQuadPayPlugin\Action\ConvertPaymentAction;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Convert;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class ConvertPaymentActionSpec extends ObjectBehavior
{
    function let(PaymentDescriptionProviderInterface $paymentDescriptionProvider): void
    {
        $this->beConstructedWith($paymentDescriptionProvider);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_executes(
        Convert $request,
        PaymentInterface $payment,
        OrderInterface $order,
        CustomerInterface $customer,
        GatewayInterface $gateway,
        PaymentDescriptionProviderInterface $paymentDescriptionProvider,
        AddressInterface $address,
        OrderItemInterface $orderItem,
        ProductInterface $product
    ): void
    {
        $this->setGateway($gateway);
        $customer->getFullName()->willReturn('Jan Kowalski');
        $customer->getEmail()->willReturn('shop@example.com');
        $customer->getId()->willReturn(1);
        $address->getPhoneNumber()->willReturn(353464674);
        $address->getCity()->willReturn('test');
        $address->getStreet()->willReturn('test');
        $address->getPostcode()->willReturn('353664');
        $address->getLastName()->willReturn('test');
        $address->getFirstName()->willReturn('test');
        $address->getProvinceCode()->willReturn(null);
        $product->getDescription()->willReturn('description');
        $product->getName()->willReturn('name');
        $product->getCode()->willReturn('code');
        $orderItem->getProduct()->willReturn($product);
        $orderItem->getQuantity()->willReturn(1);
        $orderItem->getUnitPrice()->willReturn(20);
        $order->getId()->willReturn(1);
        $order->getLocaleCode()->willReturn('pl_PL');
        $order->getCustomer()->willReturn($customer);
        $order->getShippingAddress()->willReturn($address);
        $order->getBillingAddress()->willReturn($address);
        $order->getItems()->willReturn(new ArrayCollection([$orderItem->getWrappedObject()]));
        $order->getNumber()->willReturn('0000001');
        $order->getTaxTotal()->willReturn(100);
        $order->getShippingTotal()->willReturn(100);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(445535);
        $payment->getCurrencyCode()->willReturn('EUR');
        $paymentDescriptionProvider->getPaymentDescription($payment)->willReturn('description');
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $request->setResult([
            "amount" => "445535.00",
            "consumer" => [
                "phoneNumber" => "353464674",
                "givenNames" => "test",
                "surname" => "test",
                "email" => "shop@example.com"
            ],
            "billing" => [
                "addressLine1" => "test",
                "addressLine2" => "",
                "city" => "test",
                "postcode" => "353664",
                "state" => ""
            ],
            "shipping" => [
                "addressLine1" => "test",
                "addressLine2" => "",
                "city" => "test",
                "postcode" => "353664",
                "state" => ""
            ],
            "description" => "description",
            "items" => [
                [
                    "description" => "description",
                    "name" => "name",
                    "sku" => "code",
                    "quantity" => 1,
                    "price" => "20.00"
                ]
            ],
            "merchantReference" => "0000001",
            "taxAmount" => "100.00",
            "shippingAmount" => "100.00"
            ]
        )->shouldBeCalled();

        $this->execute($request);
    }

    function it_supports_only_convert_request_payment_source_and_array_to(
        Convert $request,
        PaymentInterface $payment
    ): void {
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $this->supports($request)->shouldReturn(true);
    }
}
