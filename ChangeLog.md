# Change Log
All notable changes to this project will be documented in this file.

## Unreleased

- NEW : Prise en compte des objets Facture et Commande *12/01/2021* - 2.1.0

## Version 2.0

- FIX : Split v2.0 nécessite nomenclature v3.0 *17/11/2020* - 2.0
    La création de la v2.0 est motivée par un changement nécessaire, mais non rétrocompatible, lié au ticket 12003.
    L'utilisation de compareModuleVersion() (abricot) n'était pas possible car les numéros de version n'avaient pas été mis à jour comme il aurait fallu.
