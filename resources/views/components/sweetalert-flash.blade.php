{{-- SweetAlert Flash Messages Component --}}
{{-- Include this in your layout or pages to show Laravel flash messages as SweetAlert --}}

@if(session('success'))
<div data-flash-success="{{ session('success') }}" style="display: none;"></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#D4A373',
            confirmButtonText: 'Great!',
            timer: 3000,
            timerProgressBar: true
        });
    });
</script>
@endif

@if(session('error'))
<div data-flash-error="{{ session('error') }}" style="display: none;"></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: "{{ session('error') }}",
            confirmButtonColor: '#D4A373',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif

@if(session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: "{{ session('warning') }}",
            confirmButtonColor: '#D4A373',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif

@if(session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'info',
            title: 'Information',
            text: "{{ session('info') }}",
            confirmButtonColor: '#D4A373',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
