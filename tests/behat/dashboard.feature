@local @local_backupdrill
Feature: Backup drill administration is permission-controlled
  In order to review laboratory backup test results safely
  As a site administrator
  I can view the read-only dashboard without restoring a course in the web request

  Scenario: A site administrator can inspect test administration
    Given I log in as "admin"
    When I visit "/local/backupdrill/index.php"
    Then I should see "Backup restoration tests"
