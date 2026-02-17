@props(['name', 'label', 'options' => [], 'value' => '', 'required' => false, 'helper' => '', 'inline' => true])

<div class="mb-3">
    <label class="form-label d-block">
        {{ $label }} @if($required) <span class="text-danger">*</span> @endif
    </label>

    @foreach($options as $optionValue => $optionLabel)
        <div class="form-check {{ $inline ? 'form-check-inline' : '' }}">
            <input class="form-check-input @error($name) is-invalid @enderror" type="radio" name="{{ $name }}"
                id="{{ $name }}_{{ $optionValue }}" value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'checked' : '' }} {{ $required ? 'required' : '' }} {{ $attributes }}>
            <label class="form-check-label" for="{{ $name }}_{{ $optionValue }}">
                {{ $optionLabel }}
            </label>
        </div>
    @endforeach

    @if($helper)
        <div class="form-text">{{ $helper }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>