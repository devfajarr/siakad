@extends('layouts.app')

@section('title', 'Notifikasi Sistem')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Notifikasi Sistem</h5>
                    @if($notifications->count() > 0)
                        <form action="{{ route('notifikasi.read-all') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Tandai Semua Terbaca</button>
                        </form>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($notifications as $notification)
                            <div
                                class="list-group-item list-group-item-action py-3 {{ $notification->unread() ? 'bg-label-secondary' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            @if($notification->unread())
                                                <span class="badge badge-dot bg-danger me-2"></span>
                                            @endif
                                            <h6 class="mb-0 {{ $notification->unread() ? 'fw-bold' : '' }}">
                                                {{ $notification->data['title'] ?? ($notification->data['pesan'] ?? 'Ada Notifikasi Baru') }}
                                            </h6>
                                        </div>

                                        {{-- Struktur Lama (Pembayaran Ditolak) --}}
                                        @if(isset($notification->data['komponen']))
                                            <p class="mb-1 text-muted small">
                                                Pembayaran untuk <strong>{{ $notification->data['komponen'] ?? '-' }}</strong>
                                                sebesar
                                                Rp {{ number_format($notification->data['nominal'] ?? 0, 0, ',', '.') }} ditolak.
                                            </p>
                                            @if(!empty($notification->data['catatan_admin']))
                                                <div class="bg-light p-2 rounded mt-2 small">
                                                    <strong>Catatan Admin:</strong><br>
                                                    {{ $notification->data['catatan_admin'] }}
                                                </div>
                                            @endif
                                        @else
                                            {{-- Struktur Baru (Pesan General) --}}
                                            <p class="mb-1 text-muted small">
                                                {{ $notification->data['message'] ?? '' }}
                                            </p>
                                        @endif

                                        @if(isset($notification->data['url']))
                                            <form action="{{ route('notifikasi.read', $notification->id) }}" method="POST"
                                                class="d-inline mt-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary px-3">
                                                    <i class="ri-arrow-right-up-line me-1"></i> Lihat Detail
                                                </button>
                                            </form>
                                        @endif

                                        <small class="text-muted mt-2 d-block">
                                            <i class="ri-time-line me-1"></i>
                                            {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                        </small>
                                    </div>
                                    @if($notification->unread() && !isset($notification->data['url']))
                                        <form action="{{ route('notifikasi.read', $notification->id) }}" method="POST" class="ms-3">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                                                title="Tandai Terbaca">
                                                <i class="ri-check-double-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="ri-notification-badge-line ri-3x mb-3 text-secondary"></i>
                                <h6>Tidak ada notifikasi</h6>
                                <p class="text-muted small">Anda belum memiliki notifikasi apapun saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if($notifications->hasPages())
                    <div class="card-footer border-top">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection