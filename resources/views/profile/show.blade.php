<x-layouts.app title="Mi perfil">
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Mi perfil</h1>

        @php
            $state = $user->verificationState();
        @endphp

        <div @class([
            'card mt-6 p-5',
            'ring-emerald-200 bg-emerald-50/60' => $state === 'verified',
            'ring-amber-200 bg-amber-50/60' => $state === 'pending',
            'ring-brand-200 bg-brand-50/60' => $state === 'missing',
        ])>
            @if ($state === 'verified')
                <p class="font-semibold text-emerald-800">Identidad verificada</p>
                <p class="mt-1 text-sm text-emerald-900/80">
                    Puedes publicar coches y reservar los de otros.
                </p>
            @elseif ($state === 'pending')
                <p class="font-semibold text-amber-900">Estamos mirando tus papeles</p>
                <p class="mt-1 text-sm text-amber-900/80">
                    Mientras tanto puedes buscar coches y escribir a los dueños.
                </p>
            @else
                <p class="font-semibold text-brand-900">Te faltan los papeles</p>
                <p class="mt-1 text-sm text-brand-900/80">
                    Para alquilar un coche o publicar el tuyo tenemos que ver tu documento de
                    identidad y tu carné de conducir.
                </p>
            @endif
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
              class="card mt-4 space-y-4 p-5">
            @csrf
            @method('PATCH')

            <h2 class="font-semibold">Tus datos</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="name">Nombre</label>
                    <input id="name" name="name" class="field" value="{{ old('name', $user->name) }}" required>
                    @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="surname">Apellidos</label>
                    <input id="surname" name="surname" class="field" value="{{ old('surname', $user->surname) }}" required>
                    @error('surname')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="label" for="email">Correo</label>
                <input id="email" name="email" type="email" class="field" value="{{ old('email', $user->email) }}" required>
                @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="phone">Teléfono</label>
                    <input id="phone" name="phone" type="tel" class="field" value="{{ old('phone', $user->phone) }}" required>
                    @error('phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="birthdate">Fecha de nacimiento</label>
                    <input id="birthdate" name="birthdate" type="date" class="field"
                           value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}" required>
                    @error('birthdate')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="label" for="avatar">Foto de perfil</label>
                <input id="avatar" name="avatar" type="file" accept="image/*" class="field">
                <p class="mt-1.5 text-xs text-neutral-500">Esta sí la ve la gente. Máximo 4 MB.</p>
                @error('avatar')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>

        @if ($state !== 'verified')
            <form method="POST" action="{{ route('profile.documents') }}" enctype="multipart/form-data"
                  class="card mt-4 space-y-4 p-5">
                @csrf

                <h2 class="font-semibold">Tus papeles</h2>
                <p class="text-sm text-neutral-600">
                    Tu {{ $user->document_type ?? 'documento' }} y tu carné de conducir. Estos dos
                    <strong>no se publican en ningún sitio</strong>: se guardan aparte y sólo los ve
                    quien comprueba la cuenta.
                </p>

                <div>
                    <label class="label" for="document_photo">Documento de identidad</label>
                    <input id="document_photo" name="document_photo" type="file"
                           accept="image/*,application/pdf" class="field" required>
                    @error('document_photo')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label" for="licence_photo">Carné de conducir</label>
                    <input id="licence_photo" name="licence_photo" type="file"
                           accept="image/*,application/pdf" class="field" required>
                    @error('licence_photo')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    {{ $state === 'pending' ? 'Volver a enviarlos' : 'Enviar los papeles' }}
                </button>
            </form>
        @endif

        <form method="POST" action="{{ route('profile.password') }}" class="card mt-4 space-y-4 p-5">
            @csrf
            @method('PUT')

            <h2 class="font-semibold">Cambiar la contraseña</h2>

            <x-password-field name="current_password" label="La de ahora" autocomplete="current-password" />
            <x-password-field label="La nueva" autocomplete="new-password" hint="Ocho caracteres o más." />
            <x-password-field name="password_confirmation" label="Repite la nueva" autocomplete="new-password" />

            <button type="submit" class="btn btn-primary">Cambiar</button>
        </form>
    </div>
</x-layouts.app>
