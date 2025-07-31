<?php

namespace App\Providers;

use App\Models\Usuario;
use App\Models\Evento;
use App\Models\Invitacion;
use App\Models\Ticket;
use App\Models\Pago;

use App\Policies\UsuarioPolicy;
use App\Policies\EventoPolicy;
use App\Policies\InvitacionPolicy;
use App\Policies\TicketPolicy;
use App\Policies\PagoPolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Usuario::class => UsuarioPolicy::class,
        Evento::class => EventoPolicy::class,
        Invitacion::class => InvitacionPolicy::class,
        Ticket::class => TicketPolicy::class,
        Pago::class => PagoPolicy::class,
        Ticket::class => TicketPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}