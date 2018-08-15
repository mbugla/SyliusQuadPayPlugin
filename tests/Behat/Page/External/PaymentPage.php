<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusQuadPayPlugin\Behat\Page\External;

use Behat\Mink\Session;
use BitBag\SyliusQuadPayPlugin\Client\QuadPayApiClientInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Behat\Page\Page;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\BrowserKit\Client;

final class PaymentPage extends Page implements PaymentPageInterface
{
    /** @var RepositoryInterface */
    private $securityTokenRepository;

    /** @var Client */
    private $client;

    public function __construct(
        Session $session,
        array $parameters,
        RepositoryInterface $securityTokenRepository,
        Client $client
    ) {
        parent::__construct($session, $parameters);

        $this->securityTokenRepository = $securityTokenRepository;
        $this->client = $client;
    }

    public function capture(): void
    {
        $captureToken = $this->findToken();

        $this->getDriver()->visit($captureToken->getTargetUrl() . '?&' . http_build_query(['status' => QuadPayApiClientInterface::STATUS_ABANDONED]));
    }

    protected function getUrl(array $urlParameters = [])
    {
        return 'https://checkout.quadpay.com/checkout';
    }

    private function findToken(string $type = 'capture'): TokenInterface
    {
        $tokens = [];

        /** @var TokenInterface $token */
        foreach ($this->securityTokenRepository->findAll() as $token) {
            if (strpos($token->getTargetUrl(), $type)) {
                $tokens[] = $token;
            }
        }

        if (count($tokens) > 0) {
            return end($tokens);
        }

        throw new \RuntimeException('Cannot find capture token, check if you are after proper checkout steps');
    }
}
