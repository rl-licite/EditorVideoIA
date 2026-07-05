<x-app-layout>

<x-slot name="header">
    <h2 style="font-size:22px;font-weight:700;">IA de conteúdo</h2>
</x-slot>

<div style="padding:20px 30px 0 30px;">

    <a href="{{ route('dashboard') }}"
       style="
            display:inline-block;
            background:#2563eb;
            color:white;
            padding:10px 18px;
            border-radius:8px;
            text-decoration:none;
            font-weight:600;
            margin-bottom:20px;">
        ← Voltar ao Dashboard
    </a>

</div>

<div style="padding:30px;">

@if(session('success'))
<div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">
    {{ session('success') }}
</div>
@endif

<div style="display:grid;grid-template-columns:380px 1fr;gap:25px;">

    <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">

        <h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">
            Gerar conteúdo
        </h3>

        <form method="POST" action="{{ route('ai.generate') }}">
            @csrf

            <label>Vídeo</label>

            <select
                name="video_id"
                style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;">

                @foreach($videos as $video)
                    <option value="{{ $video->id }}">
                        {{ $video->original_name }}
                    </option>
                @endforeach

            </select>

            <label>Tema / Nicho</label>

            <input
                name="theme"
                placeholder="Ex.: produto, motivação, curiosidade"
                style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 18px;">

            <button
                style="background:#111827;color:white;border:none;border-radius:8px;padding:11px 15px;cursor:pointer;">

                Gerar títulos, roteiro e legendas

            </button>

        </form>

    </div>

    <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">

        <h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">

            Conteúdos gerados

        </h3>

        @forelse($contents as $content)

            <div style="border-bottom:1px solid #eee;padding:15px 0;">

                <strong style="font-size:18px;">
                    {{ $content->title }}
                </strong>

                <div style="font-size:13px;color:#666;margin-top:5px;">
                    {{ $content->video->original_name ?? '-' }}
                    —
                    {{ $content->type }}
                </div>

                <pre style="white-space:pre-wrap;margin-top:10px;background:#f3f4f6;padding:12px;border-radius:8px;">{{ $content->content }}</pre>

                @if($content->video)

                    <div style="margin-top:10px;">

                        <a
                            href="{{ route('ai.subtitles', $content->video) }}"
                            style="color:#2563eb;font-weight:600;text-decoration:none;">

                            Editar legendas deste vídeo

                        </a>

                    </div>

                @endif

            </div>

        @empty

            <p>Nenhum conteúdo gerado ainda.</p>

        @endforelse

        <div style="margin-top:20px;">

            {{ $contents->links() }}

        </div>

    </div>

</div>

</div>

</x-app-layout>
