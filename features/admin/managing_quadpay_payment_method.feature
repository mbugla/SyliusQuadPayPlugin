@managing_quadpay_payment_method
Feature: Adding a new QuadPay payment method
    In order to pay for orders in different ways
    As an Administrator
    I want to add a new payment method to the registry

    Background:
        Given the store operates on a channel named "Web-USD" in "USD" currency
        And I am logged in as an administrator

    @ui
    Scenario: Adding a new quadpay payment method
        Given I want to create a new QuadPay payment method
        When I name it "QuadPay" in "English (United States)"
        And I specify its code as "quadpay_test"
        And I fill the Client ID with "jdqkCbp55GRnfb9nFRz7R555pJMW444"
        And I fill the Client Secret with "jdqkCbp55GRnfb9nFRz7R555pJMW444"
        And I fill the API Endpoint with "https://quadpay-dev.auth0.com/oauth/token"
        And I fill the Auth Token Endpoint with "https://auth-dev.quadpay.com"
        And I fill the API Audience with "https://ci.quadpay.com"
        And I fill the Minimum Amount with "100.00"
        And I fill the Maximum Amount with "950.00"
        And make it available in channel "Web-USD"
        And I add it
        Then I should be notified that it has been successfully created
        And the payment method "QuadPay" should appear in the registry
