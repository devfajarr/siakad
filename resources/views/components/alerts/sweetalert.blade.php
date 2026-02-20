@if(session('success') || session('error') || session('warning') || session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Success Message
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
            @endif

            // Error Message
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
            @endif

            // Warning Message
            @if(session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: "{{ session('warning') }}",
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
            @endif

            // Info Message
            @if(session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: "{{ session('info') }}",
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
            @endif
            });
    </script>
@endif

{{-- Handle Validation Errors --}}
@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `
                        <ul style="text-align: left;" class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    `,
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light'
                },
                buttonsStyling: false
            });
        });
    </script>
@endif