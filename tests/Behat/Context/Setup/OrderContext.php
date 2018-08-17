<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusQuadPayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use BitBag\SyliusQuadPayPlugin\QuadPayGatewayFactory;
use Doctrine\Common\Persistence\ObjectManager;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class OrderContext implements Context
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var StateMachineFactoryInterface */
    private $stateMachineFactory;

    public function __construct(
        ObjectManager $objectManager,
        StateMachineFactoryInterface $stateMachineFactory
    ) {
        $this->objectManager = $objectManager;
        $this->stateMachineFactory = $stateMachineFactory;
    }

    /**
     * @Given /^(this order) with QuadPay payment is already paid$/
     */
    public function thisOrderWithQuadpayPaymentIsAlreadyPaid(OrderInterface $order): void
    {
        $this->applyMolliePaymentTransitionOnOrder($order, PaymentTransitions::TRANSITION_COMPLETE);

        $this->objectManager->flush();
    }

    private function applyMolliePaymentTransitionOnOrder(OrderInterface $order, $transition): void
    {
        foreach ($order->getPayments() as $payment) {
            /** @var PaymentMethodInterface $paymentMethod */
            $paymentMethod = $payment->getMethod();

            if (QuadPayGatewayFactory::FACTORY_NAME === $paymentMethod->getGatewayConfig()->getFactoryName()) {
                $model['orderToken'] = 'test';
                $model['orderId'] = 'test';

                $payment->setDetails($model);
            }

            $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->apply($transition);
        }
    }
}
