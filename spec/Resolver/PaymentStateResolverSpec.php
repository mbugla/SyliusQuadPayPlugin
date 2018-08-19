<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusQuadPayPlugin\Resolver;

use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClientInterface;
use BitBag\SyliusQuadPayPlugin\QuadPayGatewayFactory;
use BitBag\SyliusQuadPayPlugin\Resolver\PaymentStateResolver;
use BitBag\SyliusQuadPayPlugin\Resolver\PaymentStateResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentStateResolverSpec extends ObjectBehavior
{
    function let(
        FactoryInterface $stateMachineFactory,
        QuadPayApiClientInterface $quadPayApiClient,
        EntityManagerInterface $paymentEntityManager
    ): void {
        $this->beConstructedWith($stateMachineFactory, $quadPayApiClient, $paymentEntityManager);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(PaymentStateResolver::class);
    }

    function it_implements_payment_state_resolver_interface(): void
    {
        $this->shouldHaveType(PaymentStateResolverInterface::class);
    }

    function it_resolves(
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        QuadPayApiClientInterface $quadPayApiClient,
        FactoryInterface $stateMachineFactory,
        StateMachineInterface $stateMachine
    ): void {
        $gatewayConfig->getFactoryName()->willReturn(QuadPayGatewayFactory::FACTORY_NAME);
        $gatewayConfig->getConfig()->willReturn([
            'clientId' => 'test',
            'clientSecret' => 'test',
            'apiEndpoint' => 'https://api-ci.quadpay.com/',
            'authTokenEndpoint' => 'https://api-ci.quadpay.com/',
            'apiAudience' => 'https://api-ci.quadpay.com/',
        ]);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getAmount()->willReturn(222222);
        $payment->getDetails()->willReturn([
            'orderToken' => 'test',
        ]);
        $quadPayApiClient->setConfig(
            "test",
            "test",
            "https://api-ci.quadpay.com/",
            "https://api-ci.quadpay.com/",
            "https://api-ci.quadpay.com/"
        );
        $quadPayApiClient->getOrderByToken('test')->willReturn(['orderStatus' => QuadPayApiClientInterface::STATUS_APPROVED]);
        $stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->willReturn($stateMachine);
        $stateMachine->can(PaymentTransitions::TRANSITION_COMPLETE)->willReturn(true);

        $payment->setDetails(["orderToken" => "test", "orderStatus" => "approved"])->shouldBeCalled();
        $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE)->shouldBeCalled();

        $this->resolve($payment);
    }
}
