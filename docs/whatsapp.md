# Centrale WhatsApp-koppeling

Alle beheerders gebruiken één Whapi.Cloud-kanaal en één WhatsApp-afzender.
De verbinding draait bij Whapi; PHP-webhosting volstaat. De website verwerkt
geen inkomende WhatsApp-gesprekken en gebruikt geen workers of cronjobs.

## Uitrollen en instellen

1. Maak een databaseback-up, deploy de code en voer `php artisan migrate --force`
   uit. De aanvullende migratie maakt instellingen en verzendregistraties aan.
2. Bouw de frontend met `npm ci` en `npm run build`. Ververs bestaande Laravel-caches
   met `php artisan optimize:clear` en bouw daarna expliciet de productiecaches opnieuw:
   `php artisan config:cache`, `php artisan route:cache` en `php artisan view:cache`.
   De nieuwe WhatsApp-routes moeten in de routecache worden opgenomen; anders kan de
   link wel in de frontend staan terwijl de server een 404 teruggeeft.
3. Open **WhatsApp** in het beheerdersmenu. Verzending staat standaard uit.
4. Maak één gratis kanaal aan in het Whapi-dashboard en voer het kanaaltoken op
   de website in. Het token wordt versleuteld opgeslagen en nooit teruggestuurd.
   Behoud de bestaande `APP_KEY`: die is nodig om het token te ontsleutelen.
5. Vraag een QR-code op en scan deze op de telefoon van de gezamenlijke afzender
   onder WhatsApp → Gekoppelde apparaten. Bij aanvullende verificatie die Whapi
   niet als QR-code aanbiedt, voltooi de verificatie in het Whapi-dashboard.
6. Haal groepen op, selecteer eerst een testgroep en sla die op. Verzend expliciet
   een testbericht en controleer de ontvangst op een andere telefoon in die groep.
7. Selecteer en bewaar de voetbalgroep. Controleer de groepsnaam en schakel
   verzending in. Er wordt bij activeren zelf geen bericht verstuurd.

Instellingen hoeven niet in Plesk te worden ingevoerd. Alle huidige beheerders
kunnen deze gezamenlijke instellingen wijzigen. Vervangen van het token of
wijzigen van de groep schakelt verzending uit. Uitschakelen stopt nieuwe
verzendpogingen, maar kan een reeds gestarte provideroproep niet terugnemen.

## Gebruik en herstel

- Aanmaken: ‘Verstuur naar WhatsApp’ staat aan als de koppeling is ingericht en
  ingeschakeld. Bewerken: de checkbox staat standaard uit.
- Bij het invoeren van een wedstrijdresultaat verschijnt dezelfde checkbox alleen
  wanneer ‘Stuur beoordelingsaanvragen per e-mail’ is aangevinkt. Het bericht bevat
  de spelers en e-mailadressen van de beoordelingsverzoeken die zijn aangemaakt.
- Berichten bevatten de wedstrijddatum, teamtotalen, posities en namen uit de
  opgeslagen indeling. Individuele cijfers worden nooit verstuurd.
- ‘Geaccepteerd door WhatsApp-dienst’ is de API-bevestiging, geen afleverbevestiging.
- Bij een fout blijft de wedstrijd opgeslagen. Gebruik ‘Opnieuw proberen’ op de
  wedstrijdpagina. Bij een onzekere verzending controleer je eerst de groepsapp.
- Herstel van een achterhaalde indeling of gewijzigde koppeling is geblokkeerd.
  Open dan het wedstrijdformulier en verstuur de actuele indeling opnieuw.
- Een onderbroken proces kan ‘wordt verstuurd’ achterlaten. Na 30 seconden geldt
  dit als onzeker; ververs de wedstrijdpagina om te kunnen herstellen.
- Koppel een verbroken sessie opnieuw via de beheerpagina. Het uitschakelen van
  verzending blijft mogelijk als Whapi niet bereikbaar is.

Elke verzending heeft een unieke actiesleutel en een duurzame registratie vóór
de HTTP-aanroep. Er zijn geen automatische retries. Bij een time-out of onduidelijk
providerantwoord blijft de uitkomst onzeker: exact-eenmaal-aflevering kan een
externe berichten-API zonder bevestigde idempotentie niet garanderen.

De gratis Sandbox kent limieten en kan periodieke activiteit vereisen. Controleer
actuele voorwaarden in het Whapi-dashboard. Er wordt geen betaald kanaal of
abonnement vanuit de website aangemaakt. Het account blijft afhankelijk van een
onofficiële WhatsApp-sessie; opnieuw koppelen of accountbeperkingen zijn mogelijk.

## Verificatie

`php artisan test` gebruikt een geïsoleerde SQLite-geheugendatabase en eigen
configuratie- en routecachepaden. `WhatsappTest` simuleert alle Whapi-verzoeken;
er worden geen echte WhatsApp-berichten verstuurd. Test browserwijzigingen met een
wegwerpdatabase en testaccount, nooit met de geïmporteerde productiegegevens.

API-contract gecontroleerd via de officiële OpenAPI-specificatie:
https://panel.whapi.cloud/yaml/openapi.yaml (6 september 2026).
Gebruikte velden: `status.text`, `user.phone`/`user.id`, `base64`, `expire`,
`groups[].id/name`, `sent` en `message.id`. Groepen worden per 100 opgehaald.
