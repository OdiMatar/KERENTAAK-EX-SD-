# Klantenmodule scenario's

## Technische keuzes

- MVC: routes verwijzen naar `KlantController`, views staan in `resources/views/klanten`, data staat in model `Klant`.
- Stored procedures: MySQL gebruikt `sp_get_customers`, `sp_create_customer`, `sp_update_customer`, `sp_delete_customer` en `sp_get_customer_history`.
- Joins: `sp_get_customers` gebruikt een `LEFT JOIN` op `gebruikers`; `sp_get_customer_history` gebruikt joins met afspraken, behandelingen, medewerkers, bestellingen, bestelregels en producten.
- Try/catch: create, update en delete vangen databasefouten af en tonen een melding aan de eindgebruiker.
- Security: alleen `eigenaar` en `medewerker` mogen klanten beheren; ingelogde klanten krijgen HTTP 403.
- Validatie: client-side voorkomt lege naam, leeg adres, leeg telefoonnummer, ongeldig e-mailadres en dezelfde combinatie adres + e-mailadres; server-side validatie en stored procedures blijven als vangnet.
- Technische log: iedere overzicht-, create-, update- en deleteactie wordt gelogd via `TechnicalLogger`.

## Happy scenario's

1. Klantenoverzicht openen:
   - Medewerker of admin opent "Klanten Overzicht".
   - Het systeem haalt actieve klanten op via de database.
   - De tabel toont naam, adres, telefoonnummer en e-mailadres.

2. Klant zoeken:
   - Medewerker typt bijvoorbeeld `Lisa` in de zoekbalk.
   - De browser filtert de tabel direct client-side.
   - Alleen klanten met `Lisa` in de naam blijven zichtbaar.

3. Klant toevoegen:
   - Medewerker vult naam, adres, telefoonnummer, geldig e-mailadres, wensen en allergieën in.
   - Client-side validatie keurt de invoer goed.
   - De klant wordt opgeslagen via de stored procedure.
   - De gebruiker keert terug naar het overzicht met de melding `Klant met succes toegevoegd`.

4. Klant wijzigen:
   - Medewerker past bijvoorbeeld het telefoonnummer aan.
   - Client-side validatie keurt de invoer goed.
   - De klant wordt bijgewerkt via de stored procedure.
   - De gebruiker keert terug naar het overzicht met de melding `Klant met succes gewijzigd`.

5. Klant verwijderen:
   - Medewerker klikt op `Verwijder`.
   - De pop-up verschijnt.
   - Bij `Ja, Verwijder` wordt de klant definitief verwijderd via de stored procedure.
   - De klant verdwijnt uit de lijst en de gebruiker ziet `Klant met succes verwijderd`.

6. Klantdetails bekijken:
   - Medewerker opent `Details`.
   - De pagina toont klantgegevens, specifieke wensen, allergieën, behandelhistorie en aangeschafte producten.

## Unhappy scenario's

1. Geen zoekresultaten:
   - Medewerker zoekt op `Xyz123`.
   - De browser verbergt alle niet-matchende rijen.
   - De melding `Geen klanten gevonden die voldoen aan deze zoekterm` verschijnt.

2. Ongeldig e-mailadres:
   - Medewerker vult `lisatiko.nl` in.
   - Client-side validatie blokkeert submit.
   - De melding `Vul een geldig e-mailadres in` verschijnt.
   - Er wordt geen request verstuurd en niets opgeslagen.

3. Dubbele klant op adres en e-mailadres:
   - Medewerker gebruikt hetzelfde adres en e-mailadres als een bestaande klant.
   - Client-side validatie blokkeert submit.
   - De melding `Er bestaat al een klant met dit adres en e-mailadres` verschijnt.
   - De stored procedure controleert dit ook server-side.

4. Naam leeg bij wijzigen:
   - Medewerker maakt het naamveld leeg.
   - Client-side validatie blokkeert submit.
   - De melding `Naam is een verplicht veld en mag niet leeg zijn` verschijnt.
   - Er wordt geen request verstuurd en niets gewijzigd.

5. Klant met afspraak verwijderen:
   - Medewerker probeert een klant met gekoppelde afspraak te verwijderen.
   - De stored procedure weigert de delete.
   - De melding `Deze klant kan niet worden verwijderd omdat er afspraken aan gekoppeld zijn` verschijnt.
   - De klant blijft bestaan.

6. Verwijderen annuleren:
   - Medewerker klikt op `Verwijder`.
   - De pop-up verschijnt.
   - Bij `Nee, Terug` sluit de pop-up.
   - Er wordt geen delete-request verstuurd en de klant blijft zichtbaar.
