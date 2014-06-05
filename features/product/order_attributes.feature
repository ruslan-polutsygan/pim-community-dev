Feature: Order attributes
  In order to always have the same attribute fields order in each locale
  As a regular user
  I need to be able to set an order parameter which will manage fields order

  Background:
    Given the "default" catalog configuration
    And a "Car" product
    And I am logged in as "Mary"

  @javascript @skip
  Scenario: Successfully update an attribute position
    Given the following attribute group:
      | code    | label-en_US |
      | general | General     |
    And the following attributes:
      | label        | group   | sort order |
      | Release Date | General | 1          |
      | Color        | General | 2          |
      | Price        | General | 3          |
    When I am on the "General" attribute group page
    And I visit the "Attributes" tab
    Then the attribute "Price" should be in position 3
    And I change the attribute "Price" position to 1
    When I am on the "General" attribute group page
    And I visit the "Attributes" tab
    Then the attribute "Price" should be in position 1
    And the attribute "Color" should be in position 3

  Scenario: Display attribute fields ordered by their position
    Given the following attribute groups:
      | code    | label-en_US |
      | general | General     |
      | shape   | Shape       |
    And the following attributes:
      | label        | sort order | group   |
      | Release date | 20         | General |
      | Manufacturer | 30         | General |
      | File upload  | 10         | General |
      | Color        | 10         | Shape   |
      | Weight       | 30         | Shape   |
      | Height       | 20         | Shape   |
    And the "Car" product has the "releaseDate, manufacturer, fileUpload, color, weight and height" attributes
    When I am on the "Car" product page
    Then attributes in group "General" should be File upload, Release date and Manufacturer
    And attributes in group "Shape" should be Color, Height and Weight
