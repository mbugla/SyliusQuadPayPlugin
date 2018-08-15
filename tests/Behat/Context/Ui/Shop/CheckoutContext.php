<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusQuadPayPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Tests\BitBag\SyliusQuadPayPlugin\Behat\Mocker\QuadPayApiMocker;
use Tests\BitBag\SyliusQuadPayPlugin\Behat\Page\External\PaymentPageInterface;

final class CheckoutContext implements Context
{
    /** @var CompletePageInterface */
    private $completePage;

    /** @var ShowPageInterface */
    private $orderDetails;

    /** @var QuadPayApiMocker */
    private $quadpayApiMocker;

    /** @var PaymentPageInterface */
    private $paymentPage;

    public function __construct(
        CompletePageInterface $completePage,
        ShowPageInterface $orderDetails,
        QuadPayApiMocker $quadPayApiMocker,
        PaymentPageInterface $paymentPage
    ) {
        $this->completePage = $completePage;
        $this->orderDetails = $orderDetails;
        $this->quadpayApiMocker = $quadPayApiMocker;
        $this->paymentPage = $paymentPage;
    }

    /**
     * @When I confirm my order with QuadPay payment
     * @Given I have confirmed my order with QuadPay payment
     */
    public function iConfirmMyOrderWithQuadPayPayment(): void
    {
        $this->quadpayApiMocker->mockApiCreatePayment(function () {
            $this->completePage->confirmOrder();
        });
    }

    /**
     * @When I sign in to QuadPay and pay successfully
     */
    public function iSignInToQuadPayAndPaySuccessfully(): void
    {
        $this->quadpayApiMocker->mockApiSuccessfulPayment(function () {
            $this->paymentPage->capture();
        });
    }

    /**
     * @When I cancel my QuadPay payment
     * @Given I have cancelled QuadPay payment
     */
    public function iCancelMyQuadPayPayment(): void
    {
        $this->quadpayApiMocker->mockApiCancelledPayment(function () {
            $this->paymentPage->capture();
        });
    }

    /**
     * @When I try to pay again QuadPay payment
     */
    public function iTryToPayAgainQuadPayPayment(): void
    {
        $this->quadpayApiMocker->mockApiCreatePayment(function () {
            $this->orderDetails->pay();
        });
    }
}
