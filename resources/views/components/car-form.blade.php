@props([
    'car' => null,
    'provinces',
    'features',
    'groups',
    'action',
    'method' => 'POST',
])

@php
    // Lo que se escribió si la validación falló, y si no lo que tiene el coche.
    $value = fn (string $field, mixed $fallback = null) => old($field, $car?->{$field} ?? $fallback);
    $chosen = old('features', $car?->features->pluck('id')->all() ?? []);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-6 space-y-4">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="card space-y-4 p-5">
        <h2 class="font-semibold">El coche</h2>

        <div class="grid gap-4 sm:grid-cols-[10rem_1fr_1fr]">
            <div>
                <label class="label" for="plate">Matrícula</label>
                <input id="plate" name="plate" class="field uppercase" value="{{ $value('plate') }}"
                       placeholder="1234 ABC" required>
                @error('plate')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="brand">Marca</label>
                <input id="brand" name="brand" class="field" value="{{ $value('brand') }}" required>
                @error('brand')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="model">Modelo</label>
                <input id="model" name="model" class="field" value="{{ $value('model') }}" required>
                @error('model')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="label" for="registration_year">Año de matriculación</label>
                <input id="registration_year" name="registration_year" type="number" class="field"
                       value="{{ $value('registration_year') }}" min="1950" max="{{ date('Y') + 1 }}" required>
                @error('registration_year')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="kilometres">Kilómetros</label>
                <input id="kilometres" name="kilometres" type="number" class="field"
                       value="{{ $value('kilometres') }}" min="0" required>
                @error('kilometres')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="colour">Color</label>
                <input id="colour" name="colour" class="field" value="{{ $value('colour') }}" required>
                @error('colour')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                'fuel' => ['Combustible', config('aparcado.fuels')],
                'transmission' => ['Cambio', config('aparcado.transmissions')],
                'body_type' => ['Carrocería', config('aparcado.body_types')],
            ] as $field => [$label, $options])
                <div>
                    <label class="label" for="{{ $field }}">{{ $label }}</label>
                    <select id="{{ $field }}" name="{{ $field }}" class="field" required>
                        @foreach ($options as $option)
                            <option value="{{ $option }}" @selected($value($field) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error($field)<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="label" for="seats">Plazas</label>
                <input id="seats" name="seats" type="number" class="field" value="{{ $value('seats', 5) }}"
                       min="1" max="9" required>
                @error('seats')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="doors">Puertas</label>
                <input id="doors" name="doors" type="number" class="field" value="{{ $value('doors', 5) }}"
                       min="2" max="5" required>
                @error('doors')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="power_hp">Potencia (CV)</label>
                <input id="power_hp" name="power_hp" type="number" class="field" value="{{ $value('power_hp') }}"
                       min="20" max="1000" required>
                @error('power_hp')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-neutral-700">
            <input type="checkbox" name="has_insurance" value="1" @checked($value('has_insurance'))
                   class="h-4 w-4 rounded border-neutral-300 text-brand-600 focus:ring-brand-600">
            Tiene seguro a todo riesgo
        </label>
    </section>

    <section class="card space-y-4 p-5">
        <h2 class="font-semibold">Precio y descripción</h2>

        <div class="sm:max-w-48">
            <label class="label" for="price">Precio por día</label>
            <div class="relative">
                <input id="price" name="price" class="field pr-8" inputmode="decimal"
                       value="{{ old('price', $car ? number_format($car->price_cents / 100, 2, ',', '') : '') }}"
                       placeholder="35,00" required>
                <span class="absolute inset-y-0 right-3 flex items-center text-sm text-neutral-500">€</span>
            </div>
            @error('price_cents')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="label" for="description">Lo que quieras contar</label>
            <textarea id="description" name="description" rows="4" class="field"
                      placeholder="Si acaba de pasar la ITV, si es fácil de aparcar, lo que sea.">{{ $value('description') }}</textarea>
            @error('description')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="card space-y-4 p-5">
        <h2 class="font-semibold">Dónde está</h2>
        <p class="text-sm text-neutral-600">
            La dirección no se publica: en la ficha sólo se ve la zona en el mapa, y el
            punto de recogida lo acuerdas tú por el chat.
        </p>

        <div>
            <label class="label" for="address">Dirección</label>
            <input id="address" name="address" class="field" value="{{ $value('address') }}"
                   autocomplete="street-address" required>
            @error('address')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-[7rem_1fr_1fr]">
            <div>
                <label class="label" for="postal_code">Código postal</label>
                <input id="postal_code" name="postal_code" class="field" value="{{ $value('postal_code') }}"
                       inputmode="numeric" required>
                @error('postal_code')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="city">Ciudad</label>
                <input id="city" name="city" class="field" value="{{ $value('city') }}" required>
                @error('city')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="province_code">Provincia</label>
                <select id="province_code" name="province_code" class="field" required>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->code }}" @selected($value('province_code') === $province->code)>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
                @error('province_code')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="sm:max-w-64">
            <label class="label" for="parking_type">Dónde se recoge</label>
            <select id="parking_type" name="parking_type" class="field">
                <option value="">Sin decir</option>
                @foreach (config('aparcado.parking_types') as $option)
                    <option value="{{ $option }}" @selected($value('parking_type') === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </section>

    <section class="card space-y-4 p-5">
        <h2 class="font-semibold">Lo que lleva</h2>

        <div class="space-y-4">
            @foreach ($features as $group => $list)
                <div>
                    <h3 class="text-xs font-medium tracking-wide text-neutral-500 uppercase">
                        {{ $groups[$group] ?? $group }}
                    </h3>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach ($list as $feature)
                            <label class="flex items-center gap-2 text-sm text-neutral-700">
                                <input type="checkbox" name="features[]" value="{{ $feature->id }}"
                                       @checked(in_array($feature->id, $chosen))
                                       class="h-4 w-4 rounded border-neutral-300 text-brand-600 focus:ring-brand-600">
                                {{ $feature->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card space-y-4 p-5">
        <h2 class="font-semibold">Fotos</h2>

        @if ($car && $car->photos->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($car->photos as $index => $photo)
                    <div class="space-y-1.5">
                        <div class="aspect-[4/3] overflow-hidden rounded-xl bg-brand-50">
                            <img src="{{ $photo->url() }}" alt="" class="h-full w-full object-cover"
                                 onerror="this.hidden = true">
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-medium text-neutral-500">
                                {{ $index === 0 ? 'Portada' : 'Foto '.($index + 1) }}
                            </p>

                            {{-- Fuera del formulario grande: un formulario dentro de otro no
                                 es HTML válido y el navegador se come el de dentro. --}}
                            <button type="submit"
                                    form="quitar-foto-{{ $photo->id }}"
                                    class="text-xs font-medium text-red-700 hover:underline">
                                Quitar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div>
            <label class="label" for="photos">Añadir fotos</label>
            <input id="photos" name="photos[]" type="file" accept="image/*" multiple class="field">
            <p class="mt-1.5 text-xs text-neutral-500">
                Hasta ocho, de 4 MB cada una. La primera es la que sale en el catálogo.
            </p>
            @error('photos')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('photos.*')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="card space-y-4 p-5">
        <label class="flex items-start gap-2.5 text-sm text-neutral-700">
            <input type="checkbox" name="published" value="1" @checked($value('published', true))
                   class="mt-0.5 h-4 w-4 rounded border-neutral-300 text-brand-600 focus:ring-brand-600">
            <span>
                <span class="font-medium">Publicado en el catálogo</span>
                <span class="block text-neutral-500">
                    Quítalo la semana que lo necesites tú. Las reservas que ya haya siguen en pie.
                </span>
            </span>
        </label>
    </section>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="btn btn-primary">{{ $slot }}</button>
        <a href="{{ route('my-cars.index') }}" class="btn btn-ghost">Cancelar</a>
    </div>
</form>

{{-- Los formularios de quitar foto viven aquí, fuera del grande, y los botones de
     arriba los invocan por su `form`. --}}
@if ($car)
    @foreach ($car->photos as $photo)
        <form id="quitar-foto-{{ $photo->id }}" method="POST" class="hidden"
              action="{{ route('my-cars.photos.destroy', [$car, $photo]) }}">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
