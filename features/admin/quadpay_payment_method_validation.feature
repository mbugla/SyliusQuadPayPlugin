@managing_quadpay_payment_method
Feature: QuadPay payment method validation
    In order to avoid making mistakes when managing a payment method
    As an Administrator
    I want to be prevented from adding it without specifying required fields

    Background:
        Given the store operates on a channel named "Web-EUR" in "EUR" currency
        And I am logged in as an administrator

    @ui
    Scenario: Trying to add a new QuadPay payment method without specifying required configuration
        Given I want to create a new QuadPay payment method
        When I name it "QuadPay" in "English (United States)"
        And I add it
        Then I should be notified that "Client ID" fields cannot be blank
        And I should be notified that "Client Secret" fields cannot be blank
        Then I should be notified that "API Endpoint" fields cannot be blank
        Then I should be notified that "Auth Token Endpoint" fields cannot be blank
        Then I should be notified that "API Audience" fields cannot be blank
        Then I should be notified that "Minimum amount" fields cannot be blank
        Then I should be notified that "Maximum amount" fields cannot be blank

    @ui
    Scenario: Trying to add a new QuadPay payment method without required currency
        Given I want to create a new QuadPay payment method
        When I name it "QuadPay" in "English (United States)"
        And make it available in channel "Web-EUR"
        And I add it
        Then I should be notified that "The base currency of the channel must be USD."
