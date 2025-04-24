# Football Rating

Een applicatie voor het bijhouden en beoordelen van voetbalwedstrijden en spelers.

## Functionaliteiten

- Spelers toevoegen en beheren
- Wedstrijden aanmaken en scores bijhouden
- Spelers indelen in teams
- Spelers beoordelen na wedstrijden
- Rating-systeem gebaseerd op beoordelingen

## Installatie met Docker

1. Clone het project

```bash
git clone <repository-url>
cd react-starter-kit
```

2. Kopieer het .env.example bestand naar .env

```bash
cp .env.example .env
```

3. Start de Docker containers

```bash
docker-compose up -d
```

4. Installeer de composer packages binnen de container

```bash
docker-compose exec php composer install --ignore-platform-reqs
```

5. Installeer de NPM packages

```bash
docker-compose exec node npm install
```

6. Genereer de application key

```bash
docker-compose exec php php artisan key:generate
```

7. Voer de migraties uit

```bash
docker-compose exec php php artisan migrate
```

8. Compile de assets

```bash
docker-compose exec node npm run dev
```

## Gebruik

Na het opzetten van het project kun je de applicatie bezoeken op [http://localhost](http://localhost).

De belangrijkste functionaliteiten zijn:

- **Spelers**: Voeg spelers toe en beheer de lijst van spelers
- **Wedstrijden**: Maak nieuwe wedstrijden aan, verdeel spelers in teams, en houd de score bij
- **Beoordelingen**: Beoordeel spelers na een wedstrijd om hun ratings aan te passen

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: React met TypeScript en Inertia.js
- **Database**: MariaDB
- **Containerization**: Docker
