# Testchecklist football-app

Gebruik deze checklist voor de lokale acceptatietest en later opnieuw voor de releasecontrole.

Legenda:

- `[x]` aantoonbaar uitgevoerd via automatische test, parity-check of expliciete technische controle;
- `[ ]` nog handmatig controleren of afvinken;
- `[!]` bekende beperking of openstaand aandachtspunt.

## 1. Technische basis

- [x] Laravel start zonder de oude `Auth::routes()`-bootfout.
- [x] `npm run build` succesvol uitgevoerd.
- [x] PHPUnit-suite succesvol uitgevoerd: 41 tests geslaagd, 225 assertions.
- [x] Nederlandse vertalingen zijn als JSON gevalideerd.
- [x] MailHog draait lokaal op `http://localhost:8025`.
- [ ] Browserconsole controleren op JavaScript-fouten tijdens de belangrijkste flows.

## 2. Authenticatie

- [x] Loginpagina opent voor een gast.
- [x] Inloggen met de bestaande gebruikersnaam- en wachtwoordpayload.
- [x] Ongeldige login toont een validatiefout.
- [x] Optie “wachtwoord onthouden” wordt verwerkt.
- [x] Openbare registratie is uitgeschakeld.
- [x] Player-login wordt geweigerd met een duidelijke melding.
- [x] Gast wordt vanaf beschermde pagina’s naar login doorgestuurd.
- [x] Uitloggen beëindigt de sessie en gaat naar login.
- [x] Wachtwoord resetten via de reset-tokenflow.
- [x] Wachtwoordreset is gebruikersspecifiek bij dubbele e-mailadressen.
- [x] Resetlink aanvragen vanuit de UI en controleren in MailHog; resetlink werkt.
- [x] Resetmail gebruikt de Vrijdag-voetbalopmaak en toont geen Laravel-branding.
- [x] E-mailverificatie openen, opnieuw verzenden en verifiëren.
- [x] Wachtwoordbevestiging getest.

## 3. Dashboard en navigatie

- [x] Dashboard opent na login.
- [x] Placeholdergrafieken worden weergegeven.
- [x] Navigatie naar dashboard, wedstrijden, gegeven beoordelingen en spelers.
- [x] Uitklapmenu met gebruikersaccount en logout werkt.
- [x] Header en gebruikersaccountmenu staan rechts uitgelijnd.
- [x] Alleen admins zien de normale applicatienavigatie.

## 4. Spelers

- [x] Spelerslijst wordt als de juiste Inertia-pagina geladen.
- [x] Spelerslijst toont de rol als kleurbadge.
- [x] Rollen kunnen via speler bewerken worden gewijzigd.
- [x] Spelerslijst toont spelersnaam en gebruikersnaam afzonderlijk.
- [x] Gebruikersnaam kan via speler bewerken worden gewijzigd en blijft uniek.
- [x] Dubbele e-mailadressen blijven toegestaan.
- [x] Meerdere spelers tegelijk aanmaken.
- [x] Succesmelding gebruikt enkelvoud en meervoud correct bij spelers aanmaken.
- [x] Succesmelding bij speler bijwerken is Nederlandstalig.
- [x] Rating wordt bij opslaan naar de integer-schaal ×100 opgeslagen.
- [x] Spelertype `attacker`, `defender` en `both` wordt gevalideerd.
- [x] Validatiefouten bij meerdere spelers blijven gekoppeld aan de juiste rij.
- [x] Speler verwijderen gebruikt soft delete.
- [x] Bij verwijderen wordt de gekoppelde gebruiker ook verwijderd.
- [x] Speler herstellen zet ook de gekoppelde gebruiker terug.
- [x] Speler bewerken: naam, e-mail, rating en type wijzigen.
- [x] Spelerslijst sorteren op naam, e-mail, rating, type en aanmaakdatum.
- [x] Filter “alle spelers inclusief inactief” aan- en uitzetten.
- [x] Controleren dat het filter en de sortering behouden blijven bij navigatie.
- [x] Speler zonder gekoppelde gebruiker correct tonen.
- [x] Tabelkoppen visueel vergelijken met productie.

## 5. Wedstrijden aanmaken en beheren

- [x] Wedstrijd aanmaken maakt de wedstrijd en spelerskoppelingen aan.
- [x] Speler-rating wordt als snapshot opgeslagen in `game_player_ratings`.
- [x] Teamverdeling wijst iedere geselecteerde speler precies één team toe.
- [x] Teamverdeling werkt voor even aantallen.
- [x] Teamverdeling bewaart ongelijke teamgroottes bij een oneven aantal spelers.
- [x] Teamverdeling gebruikt positie en rating voor balans.
- [x] Datumveld valideren met een geldige datum.
- [x] Minder dan 10 spelers blokkeren.
- [x] Meer dan 12 spelers blokkeren.
- [x] Select2 opent bij het aanmaken/bewerken van een wedstrijd.
- [x] In Select2 zoeken op spelersnaam.
- [x] Meerdere spelers selecteren en verwijderen.
- [x] Teller van geselecteerde spelers controleren.
- [x] Wedstrijd bewerken met behoud van geselecteerde spelers.
- [x] Wedstrijd verwijderen en bevestigingsdialoog controleren.
- [x] Wedstrijdoverzicht sorteren op wedstrijddatum, team 1-score en team 2-score.
- [x] Wedstrijdtabel heeft geen onbedoelde grijs/wit-strepen.
- [x] Kolomkop gebruikt “Wedstrijddatum” en neutrale vetgedrukte sorteerknoppen.
- [x] Actieknoppen controleren voor een wedstrijd zonder resultaat.
- [x] Actieknoppen controleren voor een wedstrijd met resultaat.

## 6. Resultaat en rating requests

- [x] Resultaat opslaan met beide scores.
- [x] Ratingberekening gebruikt de wedstrijdsnapshot en ingediende gemiddelden.
- [x] Resultaat opslaan werkt met maximaal drie rating requests.
- [x] Rating request-mails worden technisch verstuurd.
- [x] Rating request-mail gebruikt de Vrijdag-voetbalopmaak met logo en knop.
- [x] Resultaatvelden blokkeren negatieve of ongeldige scores.
- [x] Checkbox voor rating request-mails testen.
- [x] Checkbox is uitgeschakeld nadat requests al bestaan.
- [x] Controleren dat dubbele rating requests niet ontstaan.
- [x] MailHog controleren op inhoud, ontvanger, link en vervaltijd van de e-mail.
- [x] Op de wedstrijdpagina status, verzendtijd en vervaldatum van elk beoordelingsverzoek controleren.
- [x] Een verlopen verzoek opnieuw versturen en controleren dat de nieuwe link 72 uur geldig is.
- [x] Een verzoek vervangen door een gekozen deelnemer.
- [x] Een verzoek vervangen met een lege keuze en controleren dat willekeurig een geschikte deelnemer wordt gekozen.
- [x] Controleren dat een oude link direct ongeldig is na resend of replacement.
- [x] Historie van initiële verzending, resend, replacement, fout en voltooiing controleren.
- [x] Resend en replacement voor een wedstrijd in het verleden zijn niet beschikbaar en worden server-side geweigerd.
- [x] Alleen het resultaat van de meest recente wedstrijd opnieuw bewerken en controleren dat ratings opnieuw correct worden berekend.
- [x] Controleren dat oudere wedstrijden met een resultaat geen knop “Resultaat invoeren/bewerken” tonen en niet direct toegankelijk zijn.

## 7. Beoordelingen door spelers

- [x] Geldige signed beoordelingspagina opent.
- [x] Ongeldige signed URL wordt geweigerd met 403.
- [x] De beoordelingspagina ontvangt een geldige signed POST-URL.
- [x] Decimalen worden opgeslagen als beoordeling.
- [x] Dubbele beoordeling voor dezelfde speler/wedstrijd wordt geblokkeerd.
- [x] Beoordelingsformulier toont beide teams en de wedstrijduitslag.
- [x] Eigen naam kan niet opnieuw beoordeeld worden.
- [x] Waarden 5 t/m 10 met stapgrootte 0,1 testen.
- [x] Verplichte beoordeling per speler controleren.
- [x] Beoordeling versturen via de link uit MailHog.
- [x] Bevestigingspagina na versturen controleren.
- [x] Verlopen signed link controleren met een gecontroleerde testlink.
- [x] Een voltooide aanvraag opnieuw openen en de read-only melding controleren.

## 8. Beoordelingsoverzicht

- [x] Overzicht opent met historische wedstrijden.
- [x] Beoordelingen zijn per beoordelaar gegroepeerd.
- [x] Alle spelers worden getoond, inclusief een `-` wanneer geen beoordeling bestaat.
- [x] Datumopmaak en tabelregels vergelijken met productie.
- [x] Nested beoordelingstabellen hebben geen ongewenste gestreepte rijen.

## 9. Databasepariteit en historische data

- [x] Lokale productie-snapshot gecontroleerd op 90 users, 89 players, 70 games, 834 teams, 834 snapshots, 1.458 ratings en 210 rating requests.
- [x] 22 soft-deleted spelers en gebruikers zijn behouden.
- [x] De gebruiker zonder speler is behouden.
- [x] De twee historisch ongelijke wedstrijden zijn behouden.
- [x] Er zijn geen reguliere tests die de geïmporteerde productiedatabase gebruiken.
- [x] Parity-tests zijn opt-in en read-only.
- [ ] Parity-tests opnieuw uitvoeren vlak voor release.

## 10. Deployment en livegang

- [ ] Productiebackup van database en code maken.
- [ ] Release testen tegen een kopie van de productie-database.
- [ ] Deployment-script op Plesk invullen en dry-runnen.
- [ ] `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader` uitvoeren.
- [ ] `npm ci --include=dev` uitvoeren.
- [ ] `npm run build` uitvoeren.
- [ ] `php artisan optimize:clear` en noodzakelijke caches uitvoeren.
- [ ] Document root, `public/build`, storage en permissies controleren.
- [ ] Auth-, sessie-, mail- en databaseconfiguratie controleren.
- [ ] Livegang plannen buiten actieve wedstrijden en na verloop van oude ratinglinks.
- [ ] Smoke-test uitvoeren na de switch.
- [ ] Logs en testmail controleren.
- [ ] Rollback naar de vorige codeversie verifiëren.

## Bekende aandachtspunten

- `[!]` De dashboardgrafieken gebruiken voorlopig placeholderdata.
- `[!]` De generic `game_player_ratings` resource-controller heeft geen gebruikersflow en lege methodes.
- `[!]` Browser-smoketests zijn nog geen geautomatiseerde browser-tests; de open browserpunten moeten handmatig worden afgevinkt.
- `[!]` Bestaande signed ratinglinks hoeven volgens afspraak niet behouden te blijven.
