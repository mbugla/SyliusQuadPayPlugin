<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusQuadPayPlugin\PaymentProcessing;

use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClientInterface;
use BitBag\SyliusQuadPayPlugin\QuadPayGatewayFactory;
use GuzzleHttp\Exception\ClientException;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class RefundPaymentProcessor implements PaymentProcessorInterface
{
    /** @var Session */
    private $session;

    /** @var QuadPayApiClientInterface */
    private $quadPayApiClient;

    /** @var \Faker\Generator */
    private $faker;

    public function __construct(Session $session, QuadPayApiClientInterface $quadPayApiClient)
    {
        $this->session = $session;
        $this->quadPayApiClient = $quadPayApiClient;

        $this->faker = \Faker\Factory::create();
    }

    public function process(PaymentInterface $payment): void
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        if (QuadPayGatewayFactory::FACTORY_NAME !== $paymentMethod->getGatewayConfig()->getFactoryName()) {
            return;
        }

        $details = $payment->getDetails();

        if (false === isset($payment->getDetails()['orderToken'])) {
            $this->session->getFlashBag()->add("info", "The payment refund was made only locally.");

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

        $merchantRefundReference = $this->faker->uuid;

        $details['merchantRefundReference'] = $merchantRefundReference;

        try {
            $result = $this->quadPayApiClient->refund(
                $payment->getAmount() / 100,
                $merchantRefundReference,
                $details['orderToken'],
                $details['orderId'] ?? null
            );

            $details['refundDetails'] = $result;

            $payment->setDetails($details);
        } catch (ClientException $clientException) {
            $message = $clientException->getMessage();

            if (Response::HTTP_UNPROCESSABLE_ENTITY === $clientException->getCode()) {
                $message = (string) $clientException->getResponse()->getBody();
            }

            $this->session->getFlashBag()->add("error", $message);

            throw new UpdateHandlingException();
        }
    }
}
