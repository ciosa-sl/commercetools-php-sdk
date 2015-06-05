Feature: I want to update a zone
  Background:
    Given a "zone" is identified by "id" and "version"
  Scenario: Empty update
    Given i want to update a "zone"
    Then the path should be "/zones/id"
    And the method should be "POST"
    And the request should be
    """
    {
      "version": "version",
      "actions": [
      ]
    }
    """
