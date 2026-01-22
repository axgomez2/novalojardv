<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyls.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar para Discos
        </a>
        <div class="mt-2 flex items-center gap-4">
            @if($vinyl->cover_url)
                <img src="{{ $vinyl->cover_url }}" alt="{{ $vinyl->title }}" class="h-16 w-16 rounded object-cover">
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Faixas do Disco</h1>
                <p class="text-sm text-gray-600">{{ $vinyl->title }} - {{ $vinyl->artist_names }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Track List -->
        <div class="lg:col-span-2">
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Lista de Faixas</h2>
                </div>
                
                @if($vinyl->tracks->count() > 0)
                    <ul class="divide-y divide-gray-200" id="track-list">
                        @foreach($vinyl->tracks as $track)
                            <li class="p-4 hover:bg-gray-50" data-track-id="{{ $track->id }}" x-data="{ editing: false }">
                                <div x-show="!editing" class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <span class="flex h-8 w-8 items-center justify-center rounded bg-gray-100 text-sm font-medium text-gray-600">
                                            {{ $track->position ?: $loop->iteration }}
                                        </span>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $track->name }}</p>
                                            <div class="flex items-center gap-3 text-sm text-gray-500">
                                                @if($track->duration)
                                                    <span>{{ $track->duration }}</span>
                                                @endif
                                                @if($track->hasYoutube())
                                                    <a href="{{ $track->youtube_url }}" target="_blank" class="inline-flex items-center gap-1 text-red-600 hover:text-red-700">
                                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                                                        YouTube
                                                    </a>
                                                @endif
                                                @if($track->hasAudio())
                                                    <span class="inline-flex items-center gap-1 text-green-600">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                                                        Áudio
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @if($track->hasAudio())
                                            <button type="button" onclick="playAudio('{{ $track->audio_url }}')" class="rounded p-1 text-green-600 hover:bg-green-50" title="Ouvir">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        @endif
                                        <button @click="editing = true" class="rounded p-1 text-indigo-600 hover:bg-indigo-50" title="Editar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.vinyls.tracks.destroy', [$vinyl, $track]) }}" class="inline" onsubmit="return confirm('Excluir esta faixa?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded p-1 text-red-600 hover:bg-red-50" title="Excluir">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Edit Form -->
                                <form x-show="editing" method="POST" action="{{ route('admin.vinyls.tracks.update', [$vinyl, $track]) }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid gap-3 sm:grid-cols-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Posição</label>
                                            <input type="text" name="position" value="{{ $track->position }}" placeholder="A1, B2..."
                                                   class="mt-1 w-full rounded border-gray-300 text-sm">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500">Nome *</label>
                                            <input type="text" name="name" value="{{ $track->name }}" required
                                                   class="mt-1 w-full rounded border-gray-300 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Duração</label>
                                            <input type="text" name="duration" value="{{ $track->duration }}" placeholder="3:45"
                                                   class="mt-1 w-full rounded border-gray-300 text-sm">
                                        </div>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">YouTube URL</label>
                                            <input type="url" name="youtube_url" value="{{ $track->youtube_url }}" placeholder="https://youtube.com/..."
                                                   class="mt-1 w-full rounded border-gray-300 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Áudio (MP3, WAV, OGG)</label>
                                            <input type="file" name="audio" accept=".mp3,.wav,.ogg,.m4a"
                                                   class="mt-1 w-full text-sm text-gray-500 file:mr-2 file:rounded file:border-0 file:bg-gray-100 file:px-3 file:py-1 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">
                                            @if($track->hasAudio())
                                                <p class="mt-1 text-xs text-gray-500">Atual: {{ $track->audio_original_name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        @if($track->hasAudio())
                                            <form method="POST" action="{{ route('admin.vinyls.tracks.delete-audio', [$vinyl, $track]) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm text-red-600 hover:text-red-700">Remover áudio</button>
                                            </form>
                                        @endif
                                        <button type="button" @click="editing = false" class="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                                        <button type="submit" class="rounded bg-indigo-600 px-3 py-1 text-sm text-white hover:bg-indigo-700">Salvar</button>
                                    </div>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                        <p class="mt-4 text-gray-500">Nenhuma faixa cadastrada</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Add Track Form -->
        <div>
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Adicionar Faixa</h2>
                
                <form method="POST" action="{{ route('admin.vinyls.tracks.store', $vinyl) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700">Posição</label>
                        <input type="text" name="position" id="position" value="{{ old('position') }}" placeholder="A1, B2..."
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome da Faixa *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">Duração</label>
                        <input type="text" name="duration" id="duration" value="{{ old('duration') }}" placeholder="3:45"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="youtube_url" class="block text-sm font-medium text-gray-700">Link do YouTube</label>
                        <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url') }}" placeholder="https://youtube.com/watch?v=..."
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="audio" class="block text-sm font-medium text-gray-700">Arquivo de Áudio</label>
                        <input type="file" name="audio" id="audio" accept=".mp3,.wav,.ogg,.m4a"
                               class="mt-1 w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">MP3, WAV, OGG ou M4A (máx. 20MB)</p>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Adicionar Faixa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Audio Player -->
    <div id="audio-player" class="fixed bottom-0 left-0 right-0 hidden bg-gray-900 p-4 shadow-lg lg:left-64">
        <div class="mx-auto flex max-w-4xl items-center gap-4">
            <button onclick="togglePlay()" class="rounded-full bg-white p-2 text-gray-900">
                <svg id="play-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                <svg id="pause-icon" class="hidden h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </button>
            <div class="flex-1">
                <div class="h-2 rounded-full bg-gray-700">
                    <div id="progress-bar" class="h-2 rounded-full bg-indigo-500" style="width: 0%"></div>
                </div>
            </div>
            <span id="time-display" class="text-sm text-white">0:00 / 0:00</span>
            <button onclick="closePlayer()" class="text-gray-400 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <audio id="audio-element" class="hidden"></audio>
    </div>

    <script>
        let audioElement = document.getElementById('audio-element');
        let playerDiv = document.getElementById('audio-player');
        let progressBar = document.getElementById('progress-bar');
        let timeDisplay = document.getElementById('time-display');
        let playIcon = document.getElementById('play-icon');
        let pauseIcon = document.getElementById('pause-icon');

        function playAudio(url) {
            audioElement.src = url;
            audioElement.play();
            playerDiv.classList.remove('hidden');
            playIcon.classList.add('hidden');
            pauseIcon.classList.remove('hidden');
        }

        function togglePlay() {
            if (audioElement.paused) {
                audioElement.play();
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
            } else {
                audioElement.pause();
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
            }
        }

        function closePlayer() {
            audioElement.pause();
            audioElement.src = '';
            playerDiv.classList.add('hidden');
        }

        audioElement.addEventListener('timeupdate', function() {
            let progress = (audioElement.currentTime / audioElement.duration) * 100;
            progressBar.style.width = progress + '%';
            
            let current = formatTime(audioElement.currentTime);
            let duration = formatTime(audioElement.duration);
            timeDisplay.textContent = current + ' / ' + duration;
        });

        audioElement.addEventListener('ended', function() {
            playIcon.classList.remove('hidden');
            pauseIcon.classList.add('hidden');
        });

        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            let mins = Math.floor(seconds / 60);
            let secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
    </script>
</x-admin-layout>
