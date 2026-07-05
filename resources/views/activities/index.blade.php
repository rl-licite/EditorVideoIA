<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:22px;font-weight:700;color:#111827;">
            Histórico de atividades
        </h2>
    </x-slot>

    <div style="padding:30px;">
        <div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
            <h3 style="font-size:24px;font-weight:700;margin-bottom:15px;">Registro do sistema</h3>

            @if($activities->count() === 0)
                <p style="color:#666;">Nenhuma atividade registrada ainda.</p>
            @else
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="text-align:left;border-bottom:1px solid #ddd;">
                            <th style="padding:10px;">Data</th>
                            <th style="padding:10px;">Ação</th>
                            <th style="padding:10px;">Vídeo</th>
                            <th style="padding:10px;">Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:10px;white-space:nowrap;">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                <td style="padding:10px;">
                                    <strong>{{ $activity->title }}</strong>
                                    <div style="font-size:12px;color:#666;">{{ $activity->action }}</div>
                                </td>
                                <td style="padding:10px;">
                                    @if($activity->video)
                                        <a href="{{ route('videos.show', $activity->video) }}" style="color:#2563eb;text-decoration:none;">
                                            {{ $activity->video->original_name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding:10px;color:#555;">{{ $activity->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top:20px;">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
