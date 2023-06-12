# Change Log
All notable changes to this project will be documented in this file.

## Unreleased



## Version 2.0
- FIX : change filter setting for  forgeSQLFromUniversalSearchCriteria  in $form->select_company on showliners.php line 44 - *12/06/2023* - 2.0.3  
- FIX : V16 FAMILY - *02/06/2022* - 2.0.3  
- FIX: Compatibility with Dolibarr 14.0 + remove unused files - *21/10/2021* - 2.0.2
- FIX: Not encoded URL + compatibilité V13 + Bug de l'espace avec les langs *16/06/2021* - 2.0.1
- FIX: Split v2.0 nécessite nomenclature v3.0 *17/11/2020* - 2.0
    La création de la v2.0 est motivée par un changement nécessaire, mais non rétrocompatible, lié au ticket 12003.
    L'utilisation de compareModuleVersion() (abricot) n'était pas possible car les numéros de version n'avaient pas été mis à jour comme il aurait fallu.
