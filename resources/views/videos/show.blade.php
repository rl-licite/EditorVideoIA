<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:22px;font-weight:700;color:#111827;">
            Detalhes do vídeo
        </h2>
    </x-slot>

    <div style="padding:30px;">
        @if (session('success'))
            <div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        <div style="display:grid;grid-template-columns:1.1fr .9fr;gap:25px;margin-bottom:25px;">
            <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
                <h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">Pré-visualização</h3>

                <video controls style="width:100%;border-radius:12px;background:#111827;">
                    <source src="{{ asset('storage/'.$video->path) }}" type="{{ $video->mime_type }}">
                    Seu navegador não conseguiu reproduzir este vídeo.
                </video>

                <p style="margin-top:15px;color:#555;">{{ $video->original_name }}</p>
            </div>

            <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
                <h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">Informações técnicas</h3>

                <form method="POST" action="{{ route('videos.folder.update', $video) }}" style="margin-bottom:18px;">
                    @csrf
                    @method('PATCH')

                    <label style="display:block;margin-bottom:6px;font-weight:700;">Pasta</label>
                    <select name="video_folder_id" style="width:100%;border:1px solid #ddd;border-radius:8px;padding:10px;margin-bottom:10px;">
                        <option value="">Sem pasta</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" @selected($video->video_folder_id == $folder->id)>
                                {{ $folder->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" style="background:#16a34a;color:white;border:none;border-radius:8px;padding:9px 12px;cursor:pointer;">
                        Alterar pasta
                    </button>
                </form>

                <table style="width:100%;border-collapse:collapse;">
                    <tr><td style="padding:9px;border-bottom:1px solid #eee;"><strong>Status</strong></td><td style="padding:9px;border-bottom:1px solid #eee;">{{ $video->status }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #eee;"><strong>Duração</strong></td><td style="padding:9px;border-bottom:1px solid #eee;">{{ $video->duration_formatted }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #eee;"><strong>Resolução</strong></td><td style="padding:9px;border-bottom:1px solid #eee;">{{ $video->resolution }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #eee;"><strong>Orientação</strong></td><td style="padding:9px;border-bottom:1px solid #eee;">{{ $video->aspect }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #eee;"><strong>Codec</strong></td><td style="padding:9px;border-bottom:1px solid #eee;">{{ $video->codec ?? '-' }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #eee;"><strong>FPS</strong></td><td style="padding:9px;border-bottom:1px solid #eee;">{{ $video->fps ?? '-' }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #eee;"><strong>Formato</strong></td><td style="padding:9px;border-bottom:1px solid #eee;">{{ $video->format ?? '-' }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #eee;"><strong>Tamanho</strong></td><td style="padding:9px;border-bottom:1px solid #eee;">{{ $video->size_formatted }}</td></tr>
                </table>

                <div style="display:flex;gap:10px;margin-top:20px;">
                    <form method="POST" action="{{ route('videos.analyze', $video) }}">
                        @csrf
                        <button type="submit" style="background:#111827;color:white;border:none;border-radius:8px;padding:10px 14px;cursor:pointer;">
                            Reanalisar
                        </button>
                    </form>

                    <a href="{{ route('videos.index') }}" style="background:#2563eb;color:white;padding:10px 14px;border-radius:8px;text-decoration:none;">
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
            <h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">Histórico deste vídeo</h3>

            @if($activities->count() === 0)
                <p style="color:#666;">Nenhuma atividade registrada para este vídeo.</p>
            @else
                @foreach($activities as $activity)
                    <div style="border-bottom:1px solid #eee;padding:12px 0;">
                        <strong>{{ $activity->title }}</strong>
                        <div style="font-size:13px;color:#666;">{{ $activity->created_at->format('d/m/Y H:i') }} — {{ $activity->action }}</div>
                        <p style="color:#555;margin-top:4px;">{{ $activity->description }}</p>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
