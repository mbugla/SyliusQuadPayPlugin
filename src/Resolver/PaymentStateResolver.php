<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusQuadPayPlugin\Resolver;

use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClientInterface;
use BitBag\SyliusQuadPayPlugin\QuadPayGatewayFactory;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Component\HttpFoundation\Response;

final class PaymentStateResolver implements PaymentStateResolverInterface
{
    /** @var FactoryInterface */
    private $stateMachineFactory;

    /** @var QuadPayApiClientInterface */
    private $quadPayApiClient;

    /** @var EntityManagerInterface */
    private $paymentEntityManager;

    public function __construct(
        FactoryInterface $stateMachineFactory,
        QuadPayApiClientInterface $quadPayApiClient,
        EntityManagerInterface $paymentEntityManager
    ) {
        $this->stateMachineFactory = $stateMachineFactory;
        $this->quadPayApiClient = $quadPayApiClient;
        $this->paymentEntityManager = $paymentEntityManager;
    }

    public function resolve(PaymentInterface $payment): void
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        if (QuadPayGatewayFactory::FACTORY_NAME !== $paymentMethod->getGatewayConfig()->getFactoryName()) {
            return;
        }

        $details = $payment->getDetails();

        if (false === isset($details['orderToken']) && false === isset($details['orderId'])) {
            return;
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig()->getConfig();

        $this->quadPayApiClient->setConfig(
            $gatewayConfig['clientId'],
            $gatewayConfig['clientSecret'],
            $gatewayConfig['apiEndpoint'],
            $gatewayConfig['authTokenEndpoint'],
            $gatewayConfig['apiAudience']
        );

        $paymentStateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        try {
            if (isset($details['orderId'])) {
                $order = $this->quadPayApiClient->getOrderById($details['orderId']);
            } else {
                $order = $this->quadPayApiClient->getOrderByToken($details['orderToken']);
            }

            $details['orderStatus'] = strtolower($order['orderStatus']);

            $payment->setDetails($details);
        } catch (ClientException $clientException) {
            if (Response::HTTP_NOT_FOUND === $clientException->getCode()) {
                $this->applyFail($paymentStateMachine);

                $this->paymentEntityManager->flush();
            }

            throw $clientException;
        }

        switch ($details['orderStatus']) {
            case QuadPayApiClientInterface::STATUS_CREATED:
                $this->applyProcess($paymentStateMachine);

                break;
            case QuadPayApiClientInterface::STATUS_ABANDONED:
                $this->applyCancel($paymentStateMachine);

                break;
            case QuadPayApiClientInterface::STATUS_APPROVED:
                $this->applyComplete($paymentStateMachine);

                break;
            default:
                $this->applyFail($paymentStateMachine);

                break;
        }

        $this->paymentEntityManager->flush();
    }

    private function applyProcess(StateMachineInterface $paymentStateMachine): void
    {
        if ($paymentStateMachine->can(PaymentTransitions::TRANSITION_PROCESS)) {
            $paymentStateMachine->apply(PaymentTransitions::TRANSITION_PROCESS);
        }
    }

    private function applyCancel(StateMachineInterface $paymentStateMachine): void
    {
        if ($paymentStateMachine->can(PaymentTransitions::TRANSITION_CANCEL)) {
            $paymentStateMachine->apply(PaymentTransitions::TRANSITION_CANCEL);
        }
    }

    private function applyComplete(StateMachineInterface $paymentStateMachine): void
    {
        if ($paymentStateMachine->can(PaymentTransitions::TRANSITION_COMPLETE)) {
            $paymentStateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
        }
    }

    private function applyFail(StateMachineInterface $paymentStateMachine): void
    {
        if ($paymentStateMachine->can(PaymentTransitions::TRANSITION_FAIL)) {
            $paymentStateMachine->apply(PaymentTransitions::TRANSITION_FAIL);
        }
    }
}
