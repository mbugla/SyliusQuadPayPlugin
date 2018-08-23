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
use Sylius\Behat\Context\Ui\Shop\Checkout\CheckoutAddressingContext;
use Sylius\Behat\Context\Ui\Shop\Checkout\CheckoutShippingContext;
use Sylius\Behat\Page\Shop\Account\RegisterPageInterface;
use Sylius\Behat\Page\Shop\Checkout\AddressPageInterface;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Checkout\SelectPaymentPageInterface;
use Sylius\Behat\Page\Shop\Checkout\SelectShippingPageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Sylius\Behat\Page\UnexpectedPageException;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Sylius\Component\Addressing\Model\CountryInterface;
use Tests\BitBag\SyliusQuadPayPlugin\Behat\Mocker\QuadPayApiMocker;
use Tests\BitBag\SyliusQuadPayPlugin\Behat\Page\External\PaymentPageInterface;
use Webmozart\Assert\Assert;

final class CheckoutContext implements Context
{
    /** @var CompletePageInterface */
    private $completePage;

    /** @var ShowPageInterface */
    private $orderDetails;

    /** @var QuadPayApiMocker */
    private $quadPayApiMocker;

    /** @var PaymentPageInterface */
    private $paymentPage;

    /** @var AddressPageInterface */
    private $addressPage;

    /** @var SelectPaymentPageInterface */
    private $selectPaymentPage;

    /** @var SelectShippingPageInterface */
    private $selectShippingPage;

    /** @var RegisterPageInterface */
    private $registerPage;

    /** @var CurrentPageResolverInterface */
    private $currentPageResolver;

    /** @var CheckoutAddressingContext */
    private $addressingContext;

    /** @var CheckoutShippingContext */
    private $shippingContext;

    /** @var CheckoutPaymentContext */
    private $paymentContext;

    public function __construct(
        CompletePageInterface $completePage,
        ShowPageInterface $orderDetails,
        QuadPayApiMocker $quadPayApiMocker,
        PaymentPageInterface $paymentPage,
        AddressPageInterface $addressPage,
        SelectPaymentPageInterface $selectPaymentPage,
        SelectShippingPageInterface $selectShippingPage,
        RegisterPageInterface $registerPage,
        CurrentPageResolverInterface $currentPageResolver,
        CheckoutAddressingContext $addressingContext,
        CheckoutShippingContext $shippingContext,
        CheckoutPaymentContext $paymentContext
    ) {
        $this->completePage = $completePage;
        $this->orderDetails = $orderDetails;
        $this->quadPayApiMocker = $quadPayApiMocker;
        $this->paymentPage = $paymentPage;
        $this->addressPage = $addressPage;
        $this->selectPaymentPage = $selectPaymentPage;
        $this->selectShippingPage = $selectShippingPage;
        $this->registerPage = $registerPage;
        $this->currentPageResolver = $currentPageResolver;
        $this->addressingContext = $addressingContext;
        $this->shippingContext = $shippingContext;
        $this->paymentContext = $paymentContext;
    }

    /**
     * @When I confirm my order with QuadPay payment
     * @Given I have confirmed my order with QuadPay payment
     */
    public function iConfirmMyOrderWithQuadPayPayment(): void
    {
        $this->quadPayApiMocker->mockApiCreatePayment(function () {
            $this->completePage->confirmOrder();
        });
    }

    /**
     * @When I sign in to QuadPay and pay successfully
     */
    public function iSignInToQuadPayAndPaySuccessfully(): void
    {
        $this->quadPayApiMocker->mockApiSuccessfulPayment(function () {
            $this->paymentPage->capture();
        });
    }

    /**
     * @When I cancel my QuadPay payment
     * @Given I have cancelled QuadPay payment
     */
    public function iCancelMyQuadPayPayment(): void
    {
        $this->quadPayApiMocker->mockApiCancelledPayment(function () {
            $this->paymentPage->capture();
        });
    }

    /**
     * @When I try to pay again QuadPay payment
     */
    public function iTryToPayAgainQuadPayPayment(): void
    {
        $this->quadPayApiMocker->mockApiCreatePayment(function () {
            $this->orderDetails->pay();
        });
    }

    /**
     * @Given I was at the checkout summary step
     */
    public function iWasAtTheCheckoutSummaryStep(): void
    {
        $this->addressingContext->iSpecifiedTheShippingAddress();
        $this->iProceedOrderWithShippingMethodAndPayment('Free', 'Offline');
    }

    /**
     * @Given I chose :shippingMethodName shipping method
     * @When I proceed selecting :shippingMethodName shipping method
     */
    public function iProceedSelectingShippingMethod($shippingMethodName): void
    {
        $this->iProceedSelectingShippingCountryAndShippingMethod(null, $shippingMethodName);
    }

    /**
     * @Given I have proceeded selecting :paymentMethodName payment method
     * @When I proceed selecting :paymentMethodName payment method
     */
    public function iProceedSelectingPaymentMethod($paymentMethodName): void
    {
        $this->iProceedSelectingShippingCountryAndShippingMethod();
        $this->paymentContext->iChoosePaymentMethod($paymentMethodName);
    }

    /**
     * @Given I have proceeded order with :shippingMethodName shipping method and :paymentMethodName payment
     * @When I proceed with :shippingMethodName shipping method and :paymentMethodName payment
     */
    public function iProceedOrderWithShippingMethodAndPayment($shippingMethodName, $paymentMethodName): void
    {
        $this->shippingContext->iHaveProceededSelectingShippingMethod($shippingMethodName);
        $this->paymentContext->iChoosePaymentMethod($paymentMethodName);
    }

    /**
     * @When I proceed through checkout process
     * @When I proceed through checkout process in the :localeCode locale
     */
    public function iProceedThroughCheckoutProcess($localeCode = 'en_US'): void
    {
        $this->addressingContext->iProceedSelectingShippingCountry(null, $localeCode);
        $this->shippingContext->iCompleteTheShippingStep();
        $this->paymentContext->iCompleteThePaymentStep();
    }

    /**
     * @When /^I proceed selecting ("[^"]+" as shipping country) with "([^"]+)" method$/
     */
    public function iProceedSelectingShippingCountryAndShippingMethod(CountryInterface $shippingCountry = null, $shippingMethodName = null): void
    {
        $this->addressingContext->iProceedSelectingShippingCountry($shippingCountry);
        $this->shippingContext->iHaveProceededSelectingShippingMethod($shippingMethodName ?: 'Free');
    }

    /**
     * @When /^I change shipping method to "([^"]*)"$/
     */
    public function iChangeShippingMethod($shippingMethodName): void
    {
        $this->paymentContext->iDecideToChangeMyShippingMethod();
        $this->shippingContext->iHaveProceededSelectingShippingMethod($shippingMethodName);
    }

    /**
     * @When I go to the addressing step
     */
    public function iGoToTheAddressingStep(): void
    {
        if ($this->selectShippingPage->isOpen()) {
            $this->selectShippingPage->changeAddressByStepLabel();

            return;
        }

        if ($this->selectPaymentPage->isOpen()) {
            $this->selectPaymentPage->changeAddressByStepLabel();

            return;
        }

        if ($this->completePage->isOpen()) {
            $this->completePage->changeAddress();

            return;
        }

        throw new UnexpectedPageException('It is impossible to go to addressing step from current page.');
    }

    /**
     * @When I go to the shipping step
     */
    public function iGoToTheShippingStep(): void
    {
        if ($this->selectPaymentPage->isOpen()) {
            $this->selectPaymentPage->changeShippingMethodByStepLabel();

            return;
        }

        if ($this->completePage->isOpen()) {
            $this->completePage->changeShippingMethod();

            return;
        }

        throw new UnexpectedPageException('It is impossible to go to shipping step from current page.');
    }

    /**
     * @Then the subtotal of :item item should be :price
     */
    public function theSubtotalOfItemShouldBe($item, $price): void
    {
        /** @var AddressPageInterface|SelectPaymentPageInterface|SelectShippingPageInterface|CompletePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([
            $this->addressPage,
            $this->selectPaymentPage,
            $this->selectShippingPage,
            $this->completePage,
        ]);

        Assert::eq($currentPage->getItemSubtotal($item), $price);
    }

    /**
     * @When I register with previously used :email email and :password password
     */
    public function iRegisterWithPreviouslyUsedEmailAndPassword(string $email, string $password): void
    {
        $this->registerPage->open();
        $this->registerPage->specifyEmail($email);
        $this->registerPage->specifyPassword($password);
        $this->registerPage->verifyPassword($password);
        $this->registerPage->specifyFirstName('Carrot');
        $this->registerPage->specifyLastName('Ironfoundersson');
        $this->registerPage->register();
    }
}
