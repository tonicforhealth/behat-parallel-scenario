Feature: Test

  @parallel-scenario
  Scenario: first

  @parallel-examples @parallel-wait
  Scenario Outline: second
    Examples:
      | parameter |
      | value1    |
      | value2    |
