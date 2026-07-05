<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:22px;font-weight:700;color:#111827;">
            Biblioteca de vídeos
        </h2>
    </x-slot>

    <div style="padding:30px;">
        @if (session('success'))
            <div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div>
                <h3 style="font-size:24px;font-weight:700;">Meus vídeos</h3>
                <p style="color:#555;">Organize a biblioteca por pastas para processar vídeos em lote.</p>
            </div>

            <div>
                <a href="{{ route('folders.index') }}" style="background:#16a34a;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;margin-right:8px;">
                    Pastas
                </a>
                <a href="{{ route('videos.create') }}" style="background:#111827;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;">
                    + Enviar vídeos
                </a>
            </div>
        </div>

        <div style="background:white;padding:16px;border-radius:14px;box-shadow:0 2px 8px #ddd;margin-bottom:20px;">
            <strong>Filtrar por pasta:</strong>

            <a href="{{ route('videos.index') }}" style="margin-left:12px;color:#2563eb;text-decoration:none;">Todas</a>
            <a href="{{ route('videos.index', ['folder' => 'none']) }}" style="margin-left:12px;color:#2563eb;text-decoration:none;">Sem pasta</a>

            @foreach($folders as $folder)
                <a href="{{ route('videos.index', ['folder' => $folder->id]) }}" style="margin-left:12px;color:{{ $folder->color }};text-decoration:none;">
                    {{ $folder->name }}
                </a>
            @endforeach
        </div>

        @if($videos->count() === 0)
            <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
                <p style="color:#666;">Nenhum vídeo encontrado neste filtro.</p>
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
                @foreach($videos as $video)
                    <div style="background:white;border-radius:16px;box-shadow:0 2px 10px #ddd;overflow:hidden;">
                        <div style="height:160px;background:#111827;display:flex;align-items:center;justify-content:center;">
                            @if($video->thumbnail_path)
                                <img src="{{ asset('storage/'.$video->thumbnail_path) }}" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <span style="color:white;font-size:42px;">🎬</span>
                            @endif
                        </div>

                        <div style="padding:18px;">
                            <div style="margin-bottom:8px;">
                                @if($video->folder)
                                    <span style="background:{{ $video->folder->color }};color:white;border-radius:999px;padding:4px 9px;font-size:12px;">
                                        {{ $video->folder->name }}
                                    </span>
                                @else
                                    <span style="background:#e5e7eb;color:#374151;border-radius:999px;padding:4px 9px;font-size:12px;">
                                        Sem pasta
                                    </span>
                                @endif
                            </div>

                            <h4 style="font-size:16px;font-weight:700;margin-bottom:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $video->original_name }}
                            </h4>

                            <div style="font-size:14px;color:#555;line-height:1.8;">
                                <div><strong>Status:</strong> {{ $video->status }}</div>
                                <div><strong>Duração:</strong> {{ $video->duration_formatted }}</div>
                                <div><strong>Resolução:</strong> {{ $video->resolution }}</div>
                                <div><strong>Formato:</strong> {{ $video->aspect }}</div>
                                <div><strong>Tamanho:</strong> {{ $video->size_formatted }}</div>
                            </div>

                            <div style="display:flex;gap:8px;margin-top:16px;">
                                <a href="{{ route('videos.show', $video) }}" style="background:#2563eb;color:white;padding:9px 12px;border-radius:8px;text-decoration:none;font-size:14px;">
                                    Detalhes
                                </a>

                                <form method="POST" action="{{ route('videos.destroy', $video) }}" onsubmit="return confirm('Excluir este vídeo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:#dc2626;color:white;border:none;border-radius:8px;padding:9px 12px;cursor:pointer;font-size:14px;">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:20px;">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
