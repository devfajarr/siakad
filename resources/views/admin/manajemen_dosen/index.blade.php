@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="mb-0">Manajemen Dosen & Hak Akses (RBAC)</h5>
                
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- Pencarian Keyword -->
                    <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        @if(request('role'))
                            <input type="hidden" name="role" value="{{ request('role') }}">
                        @endif
                        <div class="input-group input-group-sm">
                            {{-- <span class="input-group-text"><i class="ri-search-line"></i></span> --}}
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama/Username..." value="{{ request('search') }}">
                            <button class="btn btn-outline-primary" type="submit">Cari</button>
                            @if(request('search'))
                                <a href="{{ route('admin.users.index', request()->only('role')) }}" class="btn btn-outline-secondary">Reset</a>
                            @endif
                        </div>
                    </form>

                    <!-- Filter Jabatan -->
                    <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <label for="role" class="mb-0 text-nowrap">Filter Jabatan:</label>
                        <select name="role" id="role" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">-- Semua Jabatan --</option>
                            @foreach($allRoles as $id => $name)
                                @php
                                    $displayName = $name == 'pembimbing_akademik' ? 'Dosen PA' : ucwords(str_replace('_', ' ', $name));
                                @endphp
                                <option value="{{ $name }}" {{ request('role') == $name ? 'selected' : '' }}>{{ $displayName }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIDN / Username</th>
                            <th>Nama Dosen</th>
                            <th>Jabatan Saat Ini</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>{{ $user->username ?? '-' }}</td>
                            <td>
                                <strong>{{ $user->name }}</strong><br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </td>
                            <td>
                                @forelse($user->roles as $role)
                                    @php
                                        $displayName = $role->name == 'pembimbing_akademik' ? 'Dosen PA' : ucwords(str_replace('_', ' ', $role->name));
                                    @endphp
                                    <span class="badge rounded-pill bg-label-primary me-1 mb-1">{{ $displayName }}</span>
                                @empty
                                    <span class="text-muted fst-italic">Belum ada jabatan</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                <button type="button" 
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#assignRoleModal{{ $user->id }}"
                                    title="Atur Jabatan">
                                    <i class="ri-shield-keyhole-line ri-20px text-warning"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-light small fw-medium mb-3">
                                    <i class="ri-user-search-line ri-48px mb-3 text-muted"></i>
                                    <p class="mb-0">Tidak ada data dosen atau staf yang ditemukan sesuai kriteria.</p>
                                    @if(request('search') || request('role'))
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-label-secondary mt-3">Reset Semua Filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer pb-0">
                 {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modals for Assigning Roles -->
@foreach ($users as $user)
<div class="modal fade" id="assignRoleModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.users.assign-role', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Atur Jabatan untuk {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">Centang satu atau lebih jabatan yang akan dipegang oleh dosen ini secara bersamaan.</p>
                    <div class="row">
                        <div class="col-12 mb-4">
                            <label class="form-label">Jabatan (Multi-Select)</label>
                            <!-- Menggunakan Checkbox list atau Select2 multiple. Di sini fallback Checkbox yang robust -->
                            <div class="row">
                                @forelse($allRoles as $roleId => $roleName)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check form-check-inline mt-2">
                                            @php
                                                $displayName = $roleName == 'pembimbing_akademik' ? 'Dosen PA' : ucwords(str_replace('_', ' ', $roleName));
                                            @endphp
                                            <input class="form-check-input" type="checkbox" id="role_{{ $user->id }}_{{ $roleId }}" 
                                                name="roles[]" value="{{ $roleName }}"
                                                {{ $user->hasRole($roleName) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $user->id }}_{{ $roleId }}">{{ $displayName }}</label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12"><div class="alert alert-warning">Belum ada data Role di sistem.</div></div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jabatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
