@props(['provinces', 'filters' => []])

{{-- El día de entrada no puede ser anterior a hoy ni el de salida anterior al de
     entrada: el navegador ya lo impide con `min`, y el servidor lo vuelve a
     comprobar porque el `min` de un input se quita con dos clics. --}}
<form method="GET"
      action="{{ route('cars.index') }}"
      x-data="{ from: @js($filters['from'] ?? '') }"
      class="card grid gap-4 p-4 sm:grid-cols-[1.4fr_1fr_1fr_auto] sm:items-end sm:p-5">
    <div>
        <label class="label" for="province">Provincia</label>
        <select id="province" name="province" class="field">
            <option value="">Toda España</option>
            @foreach ($provinces as $province)
                <option value="{{ $province->code }}" @selected(($filters['province'] ?? null) === $province->code)>
                    {{ $province->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="label" for="from">Entrada</label>
        <input id="from"
               type="date"
               name="from"
               class="field"
               x-model="from"
               min="{{ now()->toDateString() }}"
               value="{{ $filters['from'] ?? '' }}">
    </div>

    <div>
        <label class="label" for="to">Salida</label>
        <input id="to"
               type="date"
               name="to"
               class="field"
               :min="from || '{{ now()->toDateString() }}'"
               value="{{ $filters['to'] ?? '' }}">
    </div>

    <button type="submit" class="btn btn-primary sm:mb-0.5">Buscar</button>

    @error('from')
        <p class="text-sm text-red-600 sm:col-span-4">{{ $message }}</p>
    @enderror

    @error('to')
        <p class="text-sm text-red-600 sm:col-span-4">{{ $message }}</p>
    @enderror

    @error('province')
        <p class="text-sm text-red-600 sm:col-span-4">{{ $message }}</p>
    @enderror
</form>
