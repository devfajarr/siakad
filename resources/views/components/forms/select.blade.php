@props(['name', 'label', 'options' => [], 'value' => '', 'required' => false, 'placeholder' => 'Pilih salah satu', 'helper' => ''])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }} @if($required) <span class="text-danger">*</span> @endif
    </label>
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'form-select ' . ($errors->has($name) ? 'is-invalid' : '')]) }} {{ $required ? 'required' : '' }}>
        <option value="" disabled {{ old($name, $value) === '' ? 'selected' : '' }}>{{ $placeholder }}</option>
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    @if($helper)
        <div class="form-text">{{ $helper }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>