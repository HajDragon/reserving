<x-layouts::app.sidebar :title="'Terms of Use'">
    <div class="mx-auto max-w-4xl px-6 py-12 lg:px-8">
        <flux:heading class="mb-2" level="1">{{ __('Terms of Use') }}</flux:heading>
        <flux:text class="mb-8 text-zinc-500">Laatst bijgewerkt: juli 2026</flux:text>

        <div class="prose prose-zinc dark:prose-invert max-w-none space-y-8">

            <section>
                <flux:heading class="mb-3" level="2">{{ __('1. Wat is dit systeem?') }}</flux:heading>
                <flux:text>
                    Dit reserveringssysteem is ontwikkeld door het <strong>Experience Lab van Summa College</strong>.
                    Het systeem helpt studenten en medewerkers om materialen en apparatuur te reserveren voor
                    onderwijsdoeleinden.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('2. Wie mag het systeem gebruiken?') }}</flux:heading>
                <flux:text>Het systeem is bedoeld voor:</flux:text>
                <ul class="mt-3 list-disc pl-6 space-y-1">
                    <li>Studenten van Summa College die materialen nodig hebben voor hun opleiding</li>
                    <li>Medewerkers van Summa College en het Experience Lab</li>
                </ul>
                <flux:text class="mt-3">
                    Je moet een geldig e-mailadres van Summa College gebruiken om een account aan te maken.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('3. Wat zijn je verplichtingen?') }}</flux:heading>
                <flux:text>Door het systeem te gebruiken ga je akkoord met het volgende:</flux:text>
                <ul class="mt-3 list-disc pl-6 space-y-1">
                    <li>Je gebruikt alleen je eigen account en deelt je inloggegevens niet met anderen</li>
                    <li>Je reserveert alleen materialen die je daadwerkelijk nodig hebt</li>
                    <li>Je behandelt gereserveerde materialen met zorg</li>
                    <li>Je brengt materialen op tijd terug op de afgesproken datum</li>
                    <li>Je meldt schade of defecten direct bij het Experience Lab</li>
                    <li>Je gebruikt het systeem alleen voor onderwijsdoeleinden</li>
                </ul>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('4. Het reserveringsproces') }}</flux:heading>
                <flux:text>Het reserveringsproces werkt als volgt:</flux:text>
                <ol class="mt-3 list-decimal pl-6 space-y-1">
                    <li>Je kiest materialen en voegt ze toe aan je winkelmandje</li>
                    <li>Je geeft aan wanneer je de materialen nodig hebt</li>
                    <li>Je stuurt je reservering in</li>
                    <li>Een beheerder beoordeelt je reservering en keurt deze goed of wijst deze af</li>
                    <li>Je ontvangt een e-mail met de uitkomst</li>
                    <li>Bij goedkeuring haal je de materialen op bij het Experience Lab</li>
                </ol>
                <flux:text class="mt-3">
                    Een goedgekeurde reservering is een afspraak. Het is belangrijk dat je de materialen ophaalt
                    en terugbrengt op de afgesproken tijden.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('5. Annuleringen en verwijdering') }}</flux:heading>
                <flux:text>
                    Je kunt een reservering annuleren zolang deze nog in de status "In behandeling" staat.
                    Als een reservering al is goedgekeurd, kun je een verwijderingsverzoek indienen.
                    Een beheerder beoordeelt dit verzoek.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('6. Aansprakelijkheid') }}</flux:heading>
                <flux:text>
                    Je bent verantwoordelijk voor de materialen die je reserveert. Bij schade of verlies
                    kun je aansprakelijk worden gesteld. Neem bij schade altijd direct contact op met het
                    Experience Lab.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('7. Account verwijderen') }}</flux:heading>
                <flux:text>
                    Je kunt je account op elk moment verwijderen via je instellingen. Houd er rekening mee
                    dat hierdoor je reserveringsgeschiedenis ook wordt verwijderd.
                </flux:text>
            </section>

            <section>
                <flux:heading class="mb-3" level="2">{{ __('8. Wijzigingen in deze voorwaarden') }}</flux:heading>
                <flux:text>
                    Wij kunnen deze gebruiksvoorwaarden bijwijzigen. Als er belangrijke wijzigingen zijn,
                    stellen wij je hiervan op de hoogte via het systeem.
                </flux:text>
            </section>
        </div>
    </div>
</x-layouts::app.sidebar>
