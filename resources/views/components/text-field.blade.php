@props([
    'name',
    'label',
    'type' => 'text',
    'hint' => null,
    'options' => null,
])

<div>
    <label class="label" for="{{ $name }}">{{ $label }}</label>

    @if ($options !== null)
        <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'field']) }}>
            @foreach ($options as $option)
                <option value="{{ $option }}" @selected(old($name) === $option)>{{ $option }}</option>
            @endforeach
        </select>
    @else
        <input id="{{ $name }}"
               name="{{ $name }}"
               type="{{ $type }}"
               value="{{ old($name) }}"
               {{ $attributes->merge(['class' => 'field']) }}>
    @endif

    @if ($hint)
        <p class="mt-1.5 text-xs text-neutral-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
