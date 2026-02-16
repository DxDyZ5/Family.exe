<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIP Gallery — EXE</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('storage/photos/tornado-svgrepo-com.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }
        ::-webkit-scrollbar { display: none; }
        html, body { scrollbar-width: none; }

        :root {
            --bg: #000; --bg-bar: rgba(0,0,0,0.9); --text: #fff; --text-muted: rgba(255,255,255,0.3);
            --text-dim: rgba(255,255,255,0.12); --border: rgba(255,255,255,0.08);
            --accent: rgba(0,255,255,0.5); --accent-line: rgba(0,255,255,0.3); --accent-glow: rgba(0,255,255,0.05);
            --card-filter: brightness(0.85) contrast(1.05); --card-hover: brightness(1) contrast(1.1);
            --overlay-bg: rgba(0,0,0,0.8); --tray-bg: rgba(10,10,10,0.98);
            --logo-filter: brightness(0) invert(1); --fab-bg: rgba(0,0,0,0.8);
            --error-color: #ff4444;
        }

        [data-theme="light"] {
            --bg: #f0f0f0; --bg-bar: rgba(245,245,245,0.95); --text: #111; --text-muted: rgba(0,0,0,0.35);
            --text-dim: rgba(0,0,0,0.1); --border: rgba(0,0,0,0.06);
            --accent: rgba(0,130,130,0.7); --accent-line: rgba(0,130,130,0.25); --accent-glow: rgba(0,130,130,0.05);
            --card-filter: brightness(1) contrast(1); --card-hover: brightness(1.02) contrast(1.05);
            --overlay-bg: rgba(255,255,255,0.85); --tray-bg: rgba(250,250,250,0.98);
            --logo-filter: brightness(0); --fab-bg: rgba(255,255,255,0.9);
            --error-color: #cc0000;
        }

        body {
            background: var(--bg); color: var(--text);
            font-family: 'Inter', -apple-system, sans-serif;
            overflow-x: hidden; min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }

        .top-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.5rem;
            background: linear-gradient(180deg, var(--bg-bar) 0%, transparent 100%);
            pointer-events: none;
        }
        .top-bar > * { pointer-events: auto; }
        .top-bar-left { display: flex; align-items: center; gap: 0.75rem; }
        .top-bar-logo { width: 28px; height: 28px; filter: var(--logo-filter); opacity: 0.6; }
        .top-bar-title {
            font-family: 'JetBrains Mono', monospace; font-size: 0.65rem; font-weight: 500;
            letter-spacing: 0.15em; text-transform: uppercase; color: var(--accent);
        }
        .top-bar-right { display: flex; align-items: center; gap: 0.6rem; }
        .top-bar-user { font-size: 0.7rem; color: var(--text-muted); font-weight: 300; }

        .ctrl-btn {
            font-family: 'JetBrains Mono', monospace; font-size: 0.55rem; letter-spacing: 0.08em;
            text-transform: uppercase; color: var(--text-muted); background: transparent;
            border: 1px solid var(--border); padding: 0.35rem 0.5rem; border-radius: 4px;
            cursor: pointer; transition: all 0.2s;
        }
        .ctrl-btn:hover { border-color: var(--accent); color: var(--accent); }

        .btn-logout {
            font-family: 'JetBrains Mono', monospace; font-size: 0.6rem; letter-spacing: 0.1em;
            text-transform: uppercase; color: var(--text-muted); background: none;
            border: 1px solid var(--border); padding: 0.35rem 0.6rem; border-radius: 4px;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-logout:hover { color: var(--error-color); border-color: var(--error-color); }

        .gallery-wrapper { overflow: hidden; }
        .gallery-track { display: flex; align-items: center; min-height: 100vh; gap: 2rem; padding: 0 10vw; will-change: transform; }

        .photo-card {
            flex-shrink: 0; position: relative; width: clamp(280px, 40vw, 500px);
            aspect-ratio: 3/4; border-radius: 4px; overflow: hidden; cursor: pointer;
        }
        .photo-card img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4,0,0.2,1), filter 0.6s ease;
            filter: var(--card-filter);
        }
        .photo-card:hover img { transform: scale(1.04); filter: var(--card-hover); }

        .photo-card.unmoderated img { filter: blur(25px) brightness(0.7); }
        .photo-card.unmoderated:hover img { filter: blur(25px) brightness(0.7); transform: none; }

        .unmod-badge {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 5;
            font-family: 'JetBrains Mono', monospace; font-size: 0.6rem; letter-spacing: 0.15em;
            text-transform: uppercase; color: var(--accent); padding: 0.5rem 1rem;
            border: 1px solid var(--accent-line); border-radius: 4px;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
        }

        .photo-overlay {
            position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem;
            background: linear-gradient(transparent 0%, var(--overlay-bg) 100%); pointer-events: none;
        }
        .photo-entry-id {
            font-family: 'JetBrains Mono', monospace; font-size: 0.55rem; font-weight: 500;
            letter-spacing: 0.2em; color: var(--accent); text-transform: uppercase; margin-bottom: 0.3rem;
        }
        .photo-timestamp { font-family: 'JetBrains Mono', monospace; font-size: 0.5rem; color: var(--text-muted); letter-spacing: 0.08em; }
        .photo-author { font-size: 0.65rem; color: var(--text-muted); font-weight: 300; margin-top: 0.25rem; }

        .photo-card::after {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: repeating-linear-gradient(0deg, transparent 0px, transparent 2px, rgba(0,0,0,0.03) 2px, rgba(0,0,0,0.03) 4px);
        }
        .photo-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px; z-index: 2;
            background: linear-gradient(90deg, transparent, var(--accent-line), transparent);
        }

        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; text-align: center; padding: 2rem; }
        .empty-state-icon { width: 80px; height: 80px; filter: var(--logo-filter); opacity: 0.1; margin-bottom: 2rem; }
        .empty-state h2 { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem; }
        .empty-state p { font-size: 0.8rem; color: var(--text-dim); font-weight: 300; }

        .upload-fab {
            position: fixed; bottom: 2rem; right: 2rem; z-index: 200; width: 56px; height: 56px;
            border-radius: 50%; border: 1px solid var(--accent-line); background: var(--fab-bg);
            backdrop-filter: blur(20px); display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.3s ease; box-shadow: 0 0 30px var(--accent-glow);
        }
        .upload-fab:hover { border-color: var(--accent); box-shadow: 0 0 40px var(--accent-glow); transform: scale(1.05); }
        .upload-fab svg { width: 22px; height: 22px; stroke: var(--accent); transition: stroke 0.2s; }

        .upload-tray {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 300;
            background: var(--tray-bg); backdrop-filter: blur(40px);
            border-top: 1px solid var(--accent-line); padding: 2rem;
            transform: translateY(100%); transition: transform 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .upload-tray.open { transform: translateY(0); }
        .upload-tray-inner { max-width: 500px; margin: 0 auto; }
        .upload-tray-title { font-family: 'JetBrains Mono', monospace; font-size: 0.6rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--accent); margin-bottom: 1.5rem; }

        .upload-dropzone {
            border: 1px dashed var(--border); border-radius: 8px; padding: 2rem; text-align: center;
            cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden;
        }
        .upload-dropzone:hover { border-color: var(--accent-line); }
        .upload-dropzone.dragover { border-color: var(--accent); }
        .upload-dropzone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .upload-dropzone-text { font-size: 0.75rem; color: var(--text-muted); font-weight: 300; }
        .upload-dropzone-hint { font-size: 0.6rem; color: var(--text-dim); margin-top: 0.5rem; }

        .upload-progress { margin-top: 1rem; height: 2px; background: var(--border); border-radius: 1px; overflow: hidden; }
        .upload-progress-bar { height: 100%; background: var(--accent); width: 0%; transition: width 0.3s ease; }

        .upload-close { position: absolute; top: 1rem; right: 1rem; background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem; transition: color 0.2s; }
        .upload-close:hover { color: var(--text); }
        .upload-status { margin-top: 0.75rem; font-size: 0.7rem; color: var(--accent); }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; padding: 1rem 2rem;
            background: linear-gradient(0deg, var(--bg-bar) 0%, transparent 100%);
            pointer-events: none; z-index: 50; display: flex; justify-content: space-between; align-items: flex-end;
        }
        .scroll-hint { font-family: 'JetBrains Mono', monospace; font-size: 0.5rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-dim); }
        .photo-count { font-family: 'JetBrains Mono', monospace; font-size: 0.5rem; color: var(--accent); letter-spacing: 0.1em; }

        .lightbox { position: fixed; inset: 0; z-index: 500; background: rgba(0,0,0,0.95); display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .lightbox.active { opacity: 1; pointer-events: auto; }
        .lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 2px; }
        .lightbox-close { position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: rgba(255,255,255,0.3); font-size: 1.5rem; cursor: pointer; }

        .delete-btn {
            position: absolute; top: 0.75rem; right: 0.75rem; z-index: 10;
            width: 28px; height: 28px; border-radius: 50%; border: 1px solid rgba(255,68,68,0.3);
            background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.2s, background 0.2s;
            color: #ff4444; font-size: 0.8rem; font-weight: 600; line-height: 1;
        }
        .photo-card:hover .delete-btn { opacity: 1; }
        .delete-btn:hover { background: rgba(255,68,68,0.2); border-color: #ff4444; }

        /* Hidden file input for FAB */
        .hidden-file-input { position: absolute; width: 0; height: 0; opacity: 0; pointer-events: none; }
    </style>
</head>
<body x-data="galleryApp()" :data-theme="theme">
    <!-- Hidden file input triggered by FAB -->
    <input type="file" class="hidden-file-input" x-ref="fabFileInput" accept="image/jpeg,image/png,image/jpg,image/gif" @change="handleFabFile($event)">

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <img src="{{ asset('storage/photos/tornado-svgrepo-com.svg') }}" alt="" class="top-bar-logo">
            <span class="top-bar-title">Family VIP // EXE</span>
        </div>
        <div class="top-bar-right">
            <button class="ctrl-btn" @click="toggleLang()" x-text="lang === 'en' ? 'ES' : 'EN'"></button>
            <button class="ctrl-btn" @click="toggleTheme()" x-text="theme === 'dark' ? '☀' : '☾'"></button>
            <span class="top-bar-user">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout" x-text="t('exit')"></button>
            </form>
        </div>
    </div>

    @if($photos->isEmpty())
        <div class="empty-state">
            <img src="{{ asset('storage/photos/tornado-svgrepo-com.svg') }}" alt="" class="empty-state-icon">
            <h2 x-text="t('noEntries')"></h2>
            <p x-text="t('uploadFirst')"></p>
        </div>
    @else
        <div class="gallery-wrapper">
            <div class="gallery-track" id="galleryTrack">
                @foreach($photos as $index => $photo)
                    <div class="photo-card {{ !$photo->is_moderated ? 'unmoderated' : '' }}" @click="openLightbox('{{ asset('storage/' . $photo->file_path) }}', {{ $photo->is_moderated ? 'true' : 'false' }})">
                        <img src="{{ asset('storage/' . $photo->file_path) }}" alt="Entry {{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}" loading="lazy">
                        @if(!$photo->is_moderated)
                            <div class="unmod-badge" x-text="t('processing')"></div>
                        @endif
                        @if(auth()->user()->is_admin)
                            <button class="delete-btn" @click.stop="deletePhoto({{ $photo->id }})" title="Delete">&times;</button>
                        @endif
                        <div class="photo-overlay">
                            <div class="photo-entry-id">Entry_{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}</div>
                            <div class="photo-timestamp">Timestamp: {{ $photo->created_at->format('Y.m.d // H:i') }}</div>
                            @if($photo->user)
                                <div class="photo-author">by {{ $photo->user->name }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Bottom Bar -->
    <div class="bottom-bar">
        <span class="scroll-hint" x-text="t('scrollHint')"></span>
        <span class="photo-count">{{ $photos->count() }} {{ Str::plural('entry', $photos->count()) }}</span>
    </div>

    <!-- Floating Upload Button — triggers file picker immediately -->
    <div class="upload-fab" @click="$refs.fabFileInput.click()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"></path>
        </svg>
    </div>

    <!-- Upload Tray (shown after file is selected with progress) -->
    <div class="upload-tray" :class="{ 'open': showUpload }" style="position: relative;">
        <button class="upload-close" @click="closeUpload()">&times;</button>
        <div class="upload-tray-inner">
            <div class="upload-tray-title">// Data Input</div>
            <div class="upload-dropzone-text" x-text="uploadFileName || t('selectFile')"></div>
            <div class="upload-progress" x-show="uploading">
                <div class="upload-progress-bar" :style="{ width: uploadProgress + '%' }"></div>
            </div>
            <div class="upload-status" x-show="uploadStatus" x-text="uploadStatus"></div>
        </div>
    </div>

    <!-- Lightbox -->
    <div class="lightbox" :class="{ 'active': lightboxSrc }" @click.self="lightboxSrc = null">
        <button class="lightbox-close" @click="lightboxSrc = null">&times;</button>
        <img :src="lightboxSrc" x-show="lightboxSrc">
    </div>

    <script>
        const gTrans = {
            en: {
                exit: 'Exit', noEntries: 'No Entries Found', uploadFirst: 'Upload the first memory to initialize the gallery.',
                scrollHint: 'Scroll to navigate →', processing: 'Processing...',
                selectFile: 'Selecting file...', networkError: 'Network error.',
                confirmDelete: 'Delete this photo?', deleted: 'Deleted. Refreshing...',
            },
            es: {
                exit: 'Salir', noEntries: 'Sin Entradas', uploadFirst: 'Sube la primera memoria para inicializar la galería.',
                scrollHint: 'Desplázate para navegar →', processing: 'Procesando...',
                selectFile: 'Seleccionando archivo...', networkError: 'Error de red.',
                confirmDelete: '¿Eliminar esta foto?', deleted: 'Eliminada. Recargando...',
            },
        };

        function galleryApp() {
            return {
                showUpload: false,
                uploading: false,
                uploadProgress: 0,
                uploadFileName: '',
                uploadStatus: '',
                lightboxSrc: null,
                theme: localStorage.getItem('vip-theme') || 'dark',
                lang: localStorage.getItem('vip-lang') || 'en',

                t(key) { return gTrans[this.lang]?.[key] || gTrans.en[key] || key; },
                toggleTheme() { this.theme = this.theme === 'dark' ? 'light' : 'dark'; localStorage.setItem('vip-theme', this.theme); },
                toggleLang() { this.lang = this.lang === 'en' ? 'es' : 'en'; localStorage.setItem('vip-lang', this.lang); },

                openLightbox(src, isModerated) {
                    if (!isModerated) return;
                    this.lightboxSrc = src;
                },

                handleFabFile(e) {
                    const file = e.target.files[0];
                    if (file) this.upload(file);
                    e.target.value = '';
                },

                async deletePhoto(id) {
                    if (!confirm(this.t('confirmDelete'))) return;
                    try {
                        const res = await fetch('/gallery/' + id, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        });
                        if (res.ok) { this.uploadStatus = this.t('deleted'); setTimeout(() => window.location.reload(), 600); }
                    } catch { /* ignore */ }
                },

                closeUpload() {
                    this.showUpload = false;
                    this.uploadFileName = '';
                    this.uploadStatus = '';
                    this.uploadProgress = 0;
                },

                async upload(file) {
                    this.uploadFileName = file.name;
                    this.showUpload = true;
                    this.uploading = true;
                    this.uploadStatus = '';
                    this.uploadProgress = 0;

                    const form = new FormData();
                    form.append('photo', file);

                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', '{{ route("gallery.upload") }}');
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                        xhr.setRequestHeader('Accept', 'application/json');

                        xhr.upload.onprogress = (e) => {
                            if (e.lengthComputable) this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                        };

                        xhr.onload = () => {
                            this.uploading = false;
                            if (xhr.status === 200) {
                                this.uploadStatus = 'Entry logged. Refreshing...';
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                try {
                                    const data = JSON.parse(xhr.responseText);
                                    this.uploadStatus = data.message || data.errors?.photo?.[0] || 'Upload failed.';
                                } catch { this.uploadStatus = 'Upload failed.'; }
                            }
                        };

                        xhr.onerror = () => { this.uploading = false; this.uploadStatus = this.t('networkError'); };
                        xhr.send(form);
                    } catch { this.uploading = false; this.uploadStatus = 'Upload failed.'; }
                },
            };
        }

        // GSAP Horizontal Scroll
        document.addEventListener('DOMContentLoaded', () => {
            const track = document.getElementById('galleryTrack');
            if (!track || track.children.length === 0) return;

            gsap.registerPlugin(ScrollTrigger);
            const totalScroll = track.scrollWidth - window.innerWidth;

            gsap.to(track, {
                x: -totalScroll, ease: 'none',
                scrollTrigger: { trigger: '.gallery-wrapper', pin: true, scrub: 1, end: () => '+=' + totalScroll, invalidateOnRefresh: true },
            });

            gsap.utils.toArray('.photo-card').forEach((card, i) => {
                gsap.from(card, { opacity: 0, y: 30, duration: 0.6, delay: i * 0.08, ease: 'power2.out' });
            });
        });
    </script>
</body>
</html>
