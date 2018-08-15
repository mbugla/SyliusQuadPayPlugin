<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusQuadPayPlugin\Action\Api;

use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClientInterface;
use Payum\Core\Exception\UnsupportedApiException;

trait ApiAwareTrait
{
    /** @var QuadPayApiClientInterface */
    protected $quadpayApiClient;

    public function setApi($quadpayApiClient): void
    {
        if (false === $quadpayApiClient instanceof QuadPayApiClientInterface) {
            throw new UnsupportedApiException('Not supported.Expected an instance of ' . QuadPayApiClientInterface::class);
        }

        $this->quadpayApiClient = $quadpayApiClient;
    }
}
