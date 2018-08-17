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
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    /** @var GenericTokenFactoryInterface|null */
    private $tokenFactory;

    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (true === isset($details['orderToken'])) {
            return;
        }

        /** @var TokenInterface $token */
        $token = $request->getToken();

        if (null === $this->tokenFactory) {
            throw new RuntimeException();
        }

        $details['merchant'] = [
            'redirectConfirmUrl' => $token->getTargetUrl(),
            'redirectCancelUrl' => $token->getTargetUrl() . '?&' . http_build_query(['status' => QuadPayApiClientInterface::STATUS_ABANDONED]),
        ];

        $order = $this->quadpayApiClient->createOrder($details->getArrayCopy());

        $details['orderToken'] = $order['token'];
        $details['orderStatus'] = QuadPayApiClientInterface::STATUS_CREATED;

        if (isset($order['orderId'])) {
            $details['orderId'] = $order['orderId'];
        }

        throw new HttpRedirect($order['redirectUrl']);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
