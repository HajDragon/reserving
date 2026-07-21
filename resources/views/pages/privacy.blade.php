<x-layouts::app.sidebar :title="'Privacyverklaring'">
    <div class="mx-auto max-w-4xl px-6 py-12 lg:px-8">
        <flux:heading class="mb-2" level="1">{{ __('Privacyverklaring') }}</flux:heading>
        <flux:text class="mb-8 text-zinc-500">Laatst bijgewerkt: juli 2026</flux:text>

        <div class="prose prose-zinc dark:prose-invert max-w-none space-y-8">

            <section>
                <flux:heading class="mb-3" level="2">{{ __('1. Wie zijn wij?') }}</flux:heading>
                <flux:text>
                    Wij zijn het <strong>Experience Lab van Summa</strong>, onderdeel van Summa College. Ons reserveringssysteem helpt
                    studenten en medewerkers om materialen en apparatuur te reserveren voor onderwijsdoeleinden.
                </flux:text>
                <flux:text class="mt-2">
                    Als je vragen hebt over deze privacyverklaring, kun je contact met ons opnemen via het Experience Lab.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('2. Welke gegevens verzamelen wij?') }}</flux:heading>
                <flux:text>Wij verzamelen de volgende gegevens als je een account aanmaakt en het systeem gebruikt:</flux:text>
                <ul class="mt-3 list-disc pl-6 space-y-1">
                    <li><strong>Naam</strong> — om je te kunnen herkennen</li>
                    <li><strong>E-mailadres</strong> — voor inloggen en het ontvangen van meldingen over je reserveringen</li>
                    <li><strong>Wachtwoord</strong> — opgeslagen als een versleutelde hash (wij kunnen je wachtwoord niet lezen)</li>
                    <li><strong>Reserveringsgegevens</strong> — welke materialen je reserveert, wanneer, en eventuele extra wensen</li>
                    <li><strong>Apparaatinformatie</strong> — voor het goed functioneren van de website (via cookies)</li>
                </ul>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('3. Waarom verzamelen wij deze gegevens?') }}</flux:heading>
                <flux:text>Wij gebruiken je gegevens voor de volgende doelen:</flux:text>
                <ul class="mt-3 list-disc pl-6 space-y-1">
                    <li>Het verwerken van je reserveringen</li>
                    <li>Het sturen van e-mails over je reserveringen (bevestiging, herinnering, goedkeuring/afwijzing)</li>
                    <li>Het beheren van het reserveringssysteem</li>
                    <li>Het beschermen van je account (tweefactorauthenticatie, wachtwoordbeveiliging)</li>
                </ul>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('4. Hoe beschermen wij je gegevens?') }}</flux:heading>
                <flux:text>Wij nemen de beveiliging van je gegevens serieus. Daarom hebben wij de volgende maatregelen genomen:</flux:text>
                <ul class="mt-3 list-disc pl-6 space-y-1">
                    <li><strong>Versleutelde wachtwoorden</strong> — je wachtwoord wordt opgeslagen als een bcrypt hash</li>
                    <li><strong>Tweefactorauthenticatie (2FA)</strong> — extra beveiliging voor je account</li>
                    <li><strong>E-mailverificatie</strong> — je e-mailadres wordt geverifieerd bij registratie</li>
                    <li><strong>CSRF-bescherming</strong> — beschermt je tegen aanvallen via formulieren</li>
                    <li><strong>Beveiligde sessies</strong> — je login-sessie wordt veilig beheerd</li>
                    <li><strong>Toegangscontrole</strong> — alleen jij kunt je eigen reserveringen zien en aanpassen</li>
                    <li><strong>Beheerderscontrole</strong> — alleen geautoriseerde beheerders hebben toegang tot het beheerpaneel</li>
                </ul>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('5. Cookies') }}</flux:heading>
                <flux:text>
                    Onze website gebruikt cookies voor het goed functioneren van de applicatie. Dit zijn functionele cookies
                    die nodig zijn voor inlogsessies en het onthouden van je voorkeuren. Wij gebruiken geen tracking cookies
                    of cookies van derden voor reclamedoeleinden.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('6. Je rechten (AVG/GDPR)') }}</flux:heading>
                <flux:text>Onder de Algemene Verordening Gegevensbescherming (AVG) heb je de volgende rechten:</flux:text>
                <ul class="mt-3 list-disc pl-6 space-y-1">
                    <li><strong>Recht op inzage</strong> — je kunt opvragen welke gegevens wij van je hebben</li>
                    <li><strong>Recht op rectificatie</strong> — je kunt je gegevens laten aanpassen als ze niet kloppen</li>
                    <li><strong>Recht op gegevensverwijdering</strong> — je kunt je account en bijbehorende gegevens laten verwijderen via je instellingen</li>
                    <li><strong>Recht op dataportabiliteit</strong> — je kunt je gegevens exporteren in een leesbaar formaat</li>
                    <li><strong>Recht om bezwaar te maken</strong> — je kunt bezwaar maken tegen verwerking van je gegevens</li>
                </ul>
                <flux:text class="mt-3">
                    Je kunt je gegevens exporteren en je account verwijderen via <strong>Instellingen</strong> in het systeem.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('7. Hoe lang bewaren wij je gegevens?') }}</flux:heading>
                <flux:text>
                    Wij bewaren je gegevens zolang je account actief is. Als je je account verwijdert, worden je
                    persoonlijke gegevens verwijderd uit ons systeem. Reserveringslogs kunnen langer bewaard worden
                    voor administratieve doeleinden, maar worden niet gekoppeld aan identificeerbare personen.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('8. Wijzigingen in deze verklaring') }}</flux:heading>
                <flux:text>
                    Wij kunnen deze privacyverklaring bijwerken als de wetgeving verandert of als ons systeem wordt
                    aangepast. De datum hierboven laat zien wanneer de verklaring voor het laatst is bijgewerkt.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('9. Contact') }}</flux:heading>
                <flux:text>
                    Als je vragen hebt over je gegevens of deze privacyverklaring, neem dan contact op met het
                    Experience Lab van Summa College.
                </flux:text>
            </section>
        </div>
    </div>
</x-layouts::app.sidebar>
