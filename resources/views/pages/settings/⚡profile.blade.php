
<?php

use App\Concerns\ProfileValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Configuración del perfil')] class extends Component {
    use ProfileValidationRules;

    public string $nombre = '';
    public string $apellidos = '';
    public string $correo = '';

    /**
     * Cargar los datos del usuario autenticado.
     */
    public function mount(): void
    {
        $usuario = Auth::user();

        $this->nombre = $usuario->nombre;
        $this->apellidos = $usuario->apellidos;
        $this->correo = $usuario->correo;
    }

    /**
     * Actualizar la información del perfil.
     */
    public function updateProfileInformation(): void
    {
        $usuario = Auth::user();

        $validated = $this->validate(
            $this->profileRules($usuario->id_usuario)
        );

        $usuario->fill($validated);
        $usuario->save();

        Flux::toast(
            variant: 'success',
            text: 'Perfil actualizado correctamente.'
        );
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        Configuración del perfil
    </flux:heading>

    <x-pages::settings.layout
        :heading="'Mi perfil'"
        :subheading="'Actualiza tu información personal y correo electrónico'"
    >
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            <flux:input
                wire:model="nombre"
                label="Nombre"
                type="text"
                required
                autofocus
                autocomplete="given-name"
            />

            <flux:input
                wire:model="apellidos"
                label="Apellidos"
                type="text"
                required
                autocomplete="family-name"
            />

            <flux:input
                wire:model="correo"
                label="Correo electrónico"
                type="email"
                required
                autocomplete="email"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button
                        variant="primary"
                        type="submit"
                        class="w-full"
                        data-test="update-profile-button"
                    >
                        Guardar cambios
                    </flux:button>
                </div>
            </div>

        </form>

        <livewire:pages::settings.delete-user-form />

    </x-pages::settings.layout>
</section>
