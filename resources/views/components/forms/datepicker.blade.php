@props(['name', 'label', 'value' => '', 'required' => false, 'placeholder' => 'YYYY-MM-DD', 'helper' => ''])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }} @if($required) <span class="text-danger">*</span> @endif
    </label>
    <input type="text" id="{{ $name }}" name="{{ $name }}"
        class="form-control flatpickr-date @error($name) is-invalid @enderror" value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }} {{ $attributes }} />
    @if($helper)
        <div class="form-text">{{ $helper }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr("#{{ $name }}", {
                    dateFormat: "Y-m-d",
                    allowInput: true
                });
            }
        });
    </script>
@endpush