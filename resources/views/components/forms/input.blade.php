@props(['name', 'label', 'type' => 'text', 'value' => '', 'required' => false, 'placeholder' => '', 'helper' => ''])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }} @if($required) <span class="text-danger">*</span> @endif
    </label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
        class="form-control @error($name) is-invalid @enderror" value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }} {{ $attributes }} />
    @if($helper)
        <div class="form-text">{{ $helper }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>