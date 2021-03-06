Feature: I want to query cartDiscounts
  Scenario: Fetch a cartDiscount by id
    Given a "cartDiscount" is identified by "id"
    Given i want to fetch a "cartDiscount"
    Then the path should be "cart-discounts/id"
    And the method should be "GET"

  Scenario: Query customers with filter applied
    Given i want to query "cartDiscounts"
    And filter them with criteria 'name="Peter"'
    Then the path should be "cart-discounts?where=name%3D%22Peter%22"
    And the method should be "GET"

  Scenario: Query customers with filter applied
    Given i want to query "cartDiscounts"
    And filter them with criteria 'name="Peter"'
    Then the path should be "cart-discounts?where=name%3D%22Peter%22"
    And the method should be "GET"

  Scenario: Query customers with limit
    Given i want to query "cartDiscounts"
    And limit the result to "10"
    Then the path should be "cart-discounts?limit=10"
    And the method should be "GET"

  Scenario: Query customers with offset
    Given i want to query "cartDiscounts"
    And offset the result with "10"
    Then the path should be "cart-discounts?offset=10"
    And the method should be "GET"

  Scenario: Query customers sorted
    Given i want to query "cartDiscounts"
    And sort them by "name"
    Then the path should be "cart-discounts?sort=name"
    And the method should be "GET"

  Scenario: Query parameters should be sorted
    Given i want to query "cartDiscounts"
    And sort them by "name"
    And offset the result with "10"
    Then the path should be "cart-discounts?offset=10&sort=name"
    And the method should be "GET"
