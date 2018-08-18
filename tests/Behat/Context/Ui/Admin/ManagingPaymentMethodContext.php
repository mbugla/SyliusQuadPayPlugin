<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusQuadPayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\BitBag\SyliusQuadPayPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingPaymentMethodContext implements Context
{
    /** @var CreatePageInterface */
    private $createPage;

    public function __construct(CreatePageInterface $createPage)
    {
        $this->createPage = $createPage;
    }

    /**
     * @Given I want to create a new QuadPay payment method
     */
    public function iWantToCreateANewQuadPayPaymentMethod(): void
    {
        $this->createPage->open(['factory' => 'quadpay']);
    }

    /**
     * @When I fill the Client ID with :clientId
     */
    public function iFillTheClientIdWith(string $clientId): void
    {
        $this->createPage->setClientId($clientId);
    }

    /**
     * @When I fill the Client Secret with :clientSecret
     */
    public function iFillTheClientSecretWith(string $clientSecret): void
    {
        $this->createPage->setClientSecret($clientSecret);
    }

    /**
     * @When I fill the API Endpoint with :arg1
     */
    public function iFillTheApiEndpointWith(string $apiEndpoint): void
    {
        $this->createPage->setApiEndpoint($apiEndpoint);
    }

    /**
     * @When I fill the Auth Token Endpoint with :authTokenEndpoint
     */
    public function iFillTheAuthTokenEndpointWith(string $authTokenEndpoint): void
    {
        $this->createPage->setAuthTokenEndpoint($authTokenEndpoint);
    }

    /**
     * @When I fill the API Audience with :apiAudience
     */
    public function iFillTheApiAudienceWith(string $apiAudience): void
    {
        $this->createPage->setApiAudience($apiAudience);
    }

    /**
     * @When I fill the Minimum Amount with :minimumAmount
     */
    public function iFillTheMinimumAmountWith(string $minimumAmount): void
    {
        $this->createPage->setMinimumAmount($minimumAmount);
    }

    /**
     * @When I fill the Maximum Amount with :maximumAmount
     */
    public function iFillTheMaximumAmountWith(string $maximumAmount)
    {
        $this->createPage->setMaximumAmount($maximumAmount);
    }

    /**
     * @Then I should be notified that :fields fields cannot be blank
     */
    public function iShouldBeNotifiedThatCannotBeBlank(string $fields): void
    {
        $fields = explode(',', $fields);

        foreach ($fields as $field) {
            Assert::true($this->createPage->containsErrorWithMessage(sprintf(
                '%s cannot be blank.',
                trim($field)
            )));
        }
    }

    /**
     * @Then I should be notified that :message
     */
    public function iShouldBeNotifiedThat(string $message): void
    {
        Assert::true($this->createPage->containsErrorWithMessage($message));
    }
}
