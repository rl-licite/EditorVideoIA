<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:22px;font-weight:700;color:#111827;">
            Pastas da biblioteca
        </h2>
    </x-slot>

    <div style="padding:30px;">
        @if (session('success'))
            <div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background:#fee2e2;color:#991b1b;padding:14px;border-radius:10px;margin-bottom:20px;">
                {{ $errors->first() }}
            </div>
        @endif

        <div style="display:grid;grid-template-columns:380px 1fr;gap:25px;">
            <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
                <h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">Criar pasta</h3>

                <form method="POST" action="{{ route('folders.store') }}">
                    @csrf

                    <label style="display:block;margin-bottom:6px;font-weight:700;">Nome da pasta</label>
                    <input type="text" name="name" placeholder="Ex.: Produtos, Cortes, Clientes..."
                           style="width:100%;border:1px solid #ddd;border-radius:8px;padding:10px;margin-bottom:15px;">

                    <label style="display:block;margin-bottom:6px;font-weight:700;">Cor</label>
                    <input type="color" name="color" value="#111827"
                           style="width:80px;height:45px;border:1px solid #ddd;border-radius:8px;margin-bottom:18px;">

                    <br>

                    <button type="submit" style="background:#111827;color:white;border:none;border-radius:8px;padding:11px 15px;cursor:pointer;">
                        Criar pasta
                    </button>
                </form>
            </div>

            <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
                <h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">Minhas pastas</h3>

                @if($folders->count() === 0)
                    <p style="color:#666;">Nenhuma pasta criada ainda.</p>
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:15px;">
                        @foreach($folders as $folder)
                            <div style="border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                    <span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:{{ $folder->color }};"></span>
                                    <strong>{{ $folder->name }}</strong>
                                </div>

                                <p style="color:#666;margin-bottom:12px;">{{ $folder->videos_count }} vídeo(s)</p>

                                <a href="{{ route('videos.index', ['folder' => $folder->id]) }}" style="color:#2563eb;text-decoration:none;margin-right:10px;">
                                    Abrir
                                </a>

                                <form method="POST" action="{{ route('folders.destroy', $folder) }}" style="display:inline;" onsubmit="return confirm('Excluir esta pasta? Os vídeos não serão apagados.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:0;">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
