# Afspraken demo

## Happy scenarios

### Afspraak aanmaken

1. Log in als klant.
2. Ga naar `/afspraken`.
3. Klik op `Maak Nieuw Afspraak`.
4. Kies een geldige combinatie:
   - Knippen: Yassin Attiah of Omar Hassan
   - Kleuren: Yassin Attiah, Sara Bakker of Noor Smit
   - Stylen: Mohammad Abdullah of Sara Bakker
   - Extensions: Amina El Idrissi of Noor Smit
5. Kies een datum vanaf morgen.
6. Kies een starttijd tussen `09:00` en `18:45`.
7. Klik op `Afspraak bevestigen`.

Resultaat: de afspraak wordt aangemaakt en staat in het afsprakenoverzicht.

### Afspraak wijzigen

1. Kies een bestaande afspraak die niet vandaag is.
2. Klik op `Wijzigen`.
3. Kies een nieuwe datum vanaf morgen.
4. Kies een geldige medewerker voor de behandeling.
5. Kies een starttijd tussen `09:00` en `18:45`.
6. Klik op `Opslaan`.

Resultaat: de afspraak krijgt de nieuwe datum, medewerker en tijd.

### Afspraak annuleren

1. Kies een bestaande afspraak die niet vandaag is.
2. Klik op `Annuleren`.
3. Bevestig de melding.

Resultaat: de afspraak krijgt status `Geannuleerd` en verdwijnt uit het geplande overzicht.

## Unhappy scenarios

### Afspraak aanmaken

- Starttijd buiten openingstijd: foutmelding `Afspraken kunnen alleen tussen 09:00 en 19:00 worden gepland`.
- Verkeerde medewerker bij behandeling: foutmelding `Deze medewerker kan deze behandeling niet uitvoeren`.
- Medewerker is al bezet: foutmelding `Deze medewerker is op dit tijdstip niet beschikbaar`.
- Datum/tijd ligt in het verleden: foutmelding `Dit tijdstip is niet beschikbaar`.

### Afspraak wijzigen

- Afspraak is vandaag: foutmelding `Deze afspraak kan op de dag zelf niet meer gewijzigd worden`.
- Verkeerde medewerker bij behandeling: foutmelding `Deze medewerker kan deze behandeling niet uitvoeren`.
- Medewerker is al bezet: foutmelding `Dit tijdstip is niet beschikbaar`.
- Nieuwe tijd ligt buiten openingstijd: foutmelding `Afspraken kunnen alleen tussen 09:00 en 19:00 worden gepland`.

### Afspraak annuleren

- Afspraak is vandaag of in het verleden: foutmelding `Deze afspraak kan niet meer geannuleerd worden`.
- Afspraak is al geannuleerd: foutmelding `Deze afspraak kan niet meer geannuleerd worden`.

## Technische log

Alle logregels staan in:

`storage/logs/appoiments technich.log`

Elke regel is JSON en bevat onder andere:

- datum en tijd
- gebruiker id
- gebruiker naam
- gebruiker e-mail
- actie
- melding
- context
- IP-adres
- browsergegevens
