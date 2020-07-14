# Banking Commission task skeleton

-System can be ran with the following command: php script.php data.csv (use paths if data file in separate folder)
-System can be tested with the following command: composer run test

-Main functionality:
  Script.php - top class with declared fee constants witch creates an instance of MainFunctions.php
  MainFunctions.php - reads data file and converts it to an array then uses FeeCalc.php for fee calculations
  FeeCalc.php - main fee calculation class witch uses Math.php methods
  MyAutoloader.php - custom autoloader for class import management
  
"# Bank_taxing_system"
