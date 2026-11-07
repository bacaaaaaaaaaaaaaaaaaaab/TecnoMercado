
<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Create an account')"
            :description="__('Enter your details below to create your account')"
        />

        <!-- Session Status -->
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Nombre -->
            <flux:input
                name="nombre"
                :label="__('Nombre')"
                :value="old('nombre')"
                type="text"
                required
                autofocus
                autocomplete="given-name"
                placeholder="Nombre"
            />

            <!-- Apellidos -->
            <flux:input
                name="apellidos"
                :label="__('Apellidos')"
                :value="old('apellidos')"
                type="text"
                required
                autocomplete="family-name"
                placeholder="Apellidos"
            />

            <!-- Correo electrónico -->
            <flux:input
                name="correo"
                :label="__('Correo electrónico')"
                :value="old('correo')"
                type="email"
                required
                autocomplete="email"
                placeholder="maria.23070008@itsmotul.edu.mx"
            />

            <!-- Contraseña -->
            <flux:input
                name="contrasena"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Contraseña')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirmar contraseña -->
            <flux:input
                name="contrasena_confirmation"
                :label="__('Confirmar contraseña')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirmar contraseña')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
