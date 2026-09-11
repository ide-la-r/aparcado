@props([
    'name' => 'password',
    'label' => 'Contraseña',
    'autocomplete' => 'current-password',
    'hint' => null,
])

{{-- El ojo para ver lo que se escribe. El campo nace como `password` en el HTML y
     Alpine cambia el tipo después: si el navegador tiene el JavaScript apagado, el
     campo sigue tapando la contraseña en lugar de enseñarla. --}}
<div x-data="{ visible: false }">
    <label class="label" for="{{ $name }}">{{ $label }}</label>

    <div class="relative">
        <input id="{{ $name }}"
               name="{{ $name }}"
               type="password"
               :type="visible ? 'text' : 'password'"
               autocomplete="{{ $autocomplete }}"
               required
               class="field pr-11">

        <button type="button"
                tabindex="-1"
                @click="visible = ! visible"
                :aria-label="visible ? 'Ocultar la contraseña' : 'Ver la contraseña'"
                :aria-pressed="visible"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-neutral-400 hover:text-neutral-700">
            <svg x-show="! visible" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z" />
                <circle cx="12" cy="12" r="2.75" />
            </svg>

            <svg x-show="visible" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z" />
                <circle cx="12" cy="12" r="2.75" />
                <path stroke-linecap="round" d="M4 20 20 4" />
            </svg>
        </button>
    </div>

    @if ($hint)
        <p class="mt-1.5 text-xs text-neutral-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
