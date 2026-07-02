<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $latestLogs = DB::table('technical_logs')
            ->leftJoin('users', 'technical_logs.user_id', '=', 'users.id')
            ->select([
                'technical_logs.action',
                'technical_logs.message',
                'technical_logs.created_at',
                'users.name as user_name',
            ])
            ->latest('technical_logs.created_at')
            ->limit(8)
            ->get();

        return view('dashboard.index', [
            'latestLogs' => $latestLogs,
            'modules' => $this->modulesFor($user),
            'reports' => $this->reportsFor($user),
        ]);
    }

    /**
     * @return array<int, array{title: string, description: string, badge: string, route?: string}>
     */
    private function modulesFor(User $user): array
    {
        if ($user->isOwner()) {
            return [
                ['title' => 'Medewerkers beheren', 'description' => 'Nieuwe medewerkers toevoegen, wijzigen, verwijderen of blokkeren.', 'badge' => 'Eigenaar'],
                ['title' => 'Klanten beheren', 'description' => 'Klantgegevens, wensen, allergieen en historie beheren.', 'badge' => 'Eigenaar'],
                ['title' => 'Afspraken beheren', 'description' => 'Alle agendas bekijken en afspraken plannen, wijzigen of annuleren.', 'badge' => 'Planning', 'route' => 'appointments.index'],
                ['title' => 'Producten en voorraad', 'description' => 'EAN-code, leverancier, voorraad en lage voorraad controleren.', 'badge' => 'Voorraad'],
                ['title' => 'Behandelingen', 'description' => 'Prijzen, tijdsduur, specialisten en benodigde producten beheren.', 'badge' => 'Salon'],
                ['title' => 'Bestellingen', 'description' => 'Klantorders, verwachte leverdatum en orderstatus volgen.', 'badge' => 'Orders'],
            ];
        }

        if ($user->isEmployee()) {
            return [
                ['title' => 'Afspraken plannen', 'description' => 'Afspraken inplannen en beschikbaarheid van specialisten controleren.', 'badge' => 'Planning', 'route' => 'appointments.index'],
                ['title' => 'Klantgegevens', 'description' => 'Contactgegevens, wensen, allergieen en behandelhistorie bekijken.', 'badge' => 'Klanten'],
                ['title' => 'Productverkoop', 'description' => 'Producten verkopen en voorraad na verkoop bijwerken.', 'badge' => 'Voorraad'],
                ['title' => 'Behandelingen uitvoeren', 'description' => 'Inzien welke producten nodig zijn voor de gekozen behandeling.', 'badge' => 'Salon'],
            ];
        }

        return [
            ['title' => 'Afspraak maken', 'description' => 'Kies behandeling, specialist, beschikbare datum en starttijd.', 'badge' => 'Online', 'route' => 'appointments.create'],
            ['title' => 'Mijn gegevens', 'description' => 'Bekijk en wijzig je eigen naam, telefoonnummer, e-mail en voorkeuren.', 'badge' => 'Profiel'],
            ['title' => 'Producten bestellen', 'description' => 'Bestel haarproducten online en haal ze later op in de salon.', 'badge' => 'Afhalen'],
            ['title' => 'Mijn historie', 'description' => 'Bekijk eerdere behandelingen en aangeschafte producten.', 'badge' => 'Historie'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function reportsFor(User $user): array
    {
        if (! $user->isOwner()) {
            return [];
        }

        return [
            'Maandoverzicht behandelingen per medewerker',
            'Maandoverzicht productverkopen per categorie',
            'Overzicht producten per leverancier',
        ];
    }
}
