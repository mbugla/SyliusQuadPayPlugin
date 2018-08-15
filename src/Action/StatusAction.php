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

use BitBag\SyliusQuadPayPlugin\Action\Api\ApiAwareTrait;
use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClientInterface;
use GuzzleHttp\Exception\ClientException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Response;

final class StatusAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $details = $payment->getDetails();

        if (!isset($details['orderToken'])) {
            $request->markNew();

            return;
        }

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        try {
            $order = $this->quadpayApiClient->getOrder($details['orderToken']);

            $details['orderStatus'] = strtolower($order['orderStatus']);
        } catch (ClientException $clientException) {
            if (
                (Response::HTTP_NOT_FOUND === $clientException->getCode() &&
                isset($httpRequest->query['status']) && QuadPayApiClientInterface::STATUS_ABANDONED === $httpRequest->query['status']) ||
                QuadPayApiClientInterface::STATUS_ABANDONED === $details['orderStatus']
            ) {
                $details['orderStatus'] = QuadPayApiClientInterface::STATUS_ABANDONED;
            } else {
                $details['orderStatus'] = QuadPayApiClientInterface::STATUS_DECLINED;
            }
        }

        $payment->setDetails($details);

        switch ($details['orderStatus']) {
            case QuadPayApiClientInterface::STATUS_CREATED:
                $request->markPending();
                break;
            case QuadPayApiClientInterface::STATUS_ABANDONED:
                $request->markCanceled();
                break;
            case QuadPayApiClientInterface::STATUS_DECLINED:
                $request->markFailed();
                break;
            case QuadPayApiClientInterface::STATUS_APPROVED:
                $request->markCaptured();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface
        ;
    }
}
