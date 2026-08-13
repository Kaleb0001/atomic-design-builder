<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="hybridEditor({ state: $wire.entangle('{{ $getStatePath() }}'), blocks: @js($getBlocks()), canWriteCode: @js($canWriteCode()) })"
        class="fi-hybrid-editor-field"
    >
        <div x-show="canWriteCode" class="ade-tabs" style="margin-bottom: 1rem;">
            <button type="button" @click="setMode('visual')" class="ade-tab" :class="mode === 'visual' && 'is-active'">Visuel</button>
            <button type="button" @click="setMode('code')" class="ade-tab" :class="mode === 'code' && 'is-active'">Code</button>
        </div>

        <p x-show="loadError" x-text="'Erreur : ' + loadError" style="padding: 0.75rem 1rem; color: #f87171; font-size: 0.875rem; background: rgb(248 113 113 / 0.08); border-radius: 0.5rem;"></p>

        <!-- Mode visuel (GrapesJS) -->
        <div x-show="mode === 'visual'">
            <p class="ade-label" x-show="!grapesReady">Chargement de l'éditeur…</p>
            <div x-show="grapesReady">
                <p class="ade-label">Structure — les blocs 🧩 sont des repères vers des composants, pas le rendu final</p>
                <div style="display: grid; grid-template-columns: 220px 1fr; gap: 1rem;">
                    <div x-ref="blocks" class="ade-blocks-panel"></div>
                    <div x-ref="canvas"></div>
                </div>
            </div>
        </div>

        <!-- Mode code (Monaco) -->
        <div x-show="mode === 'code'">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <div class="ade-tabs">
                    <button type="button" @click="switchTab('html')" class="ade-tab" :class="activeTab === 'html' && 'is-active'">HTML</button>
                    <button type="button" @click="switchTab('css')" class="ade-tab" :class="activeTab === 'css' && 'is-active'">CSS</button>
                    <button type="button" @click="switchTab('js')" class="ade-tab" :class="activeTab === 'js' && 'is-active'">JS</button>
                </div>
                <button type="button" @click="codeLayout = (codeLayout === 'side' ? 'stacked' : 'side')" class="ade-link-btn">
                    <span x-text="codeLayout === 'side' ? 'Aperçu en dessous' : 'Aperçu à droite'"></span>
                </button>
            </div>

            <div :style="codeLayout === 'side' ? 'display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;' : 'display: grid; grid-template-columns: 1fr; gap: 1rem;'">
                <div x-ref="monacoContainer" style="height: 420px; border: 1px solid rgb(127 127 127 / 0.25); border-radius: 0.5rem; overflow: hidden; position: relative;">
                    <p class="ade-label" x-show="!monacoReady" style="padding: 1rem;">Chargement de l'éditeur…</p>
                </div>
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.375rem;">
                        <p class="ade-label" style="margin: 0;">Rendu final (composants résolus)</p>
                        <button type="button" @click="refreshCodePreview()" class="ade-link-btn">
                            <span x-show="syncing" class="ade-spinner"></span>
                            <span x-text="syncing ? 'Actualisation…' : 'Actualiser'"></span>
                        </button>
                    </div>
                    <iframe x-ref="codePreview" sandbox="allow-scripts" style="width: 100%; height: 420px; border: 1px solid rgb(127 127 127 / 0.25); border-radius: 0.5rem; background: white;"></iframe>
                </div>
            </div>
        </div>

        <!-- Aperçu résolu partagé, sous le mode visuel (le mode code a le sien, ci-dessus) -->
        <div x-show="mode === 'visual'" style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid rgb(127 127 127 / 0.2);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.375rem;">
                <p class="ade-label" style="margin: 0;">Rendu final (composants résolus)</p>
                <button type="button" @click="refreshVisualPreview()" class="ade-link-btn">
                    <span x-show="syncing" class="ade-spinner"></span>
                    <span x-text="syncing ? 'Actualisation…' : 'Actualiser'"></span>
                </button>
            </div>
            <iframe x-ref="visualPreview" sandbox="allow-scripts" style="width: 100%; height: 300px; border: 1px solid rgb(127 127 127 / 0.25); border-radius: 0.5rem; background: white;"></iframe>
        </div>
    </div>

    @once
        <style>
            .fi-hybrid-editor-field .ade-tabs {
                display: inline-flex;
                background: rgb(127 127 127 / 0.1);
                border-radius: 0.5rem;
                padding: 3px;
                gap: 2px;
            }
            .fi-hybrid-editor-field .ade-tab {
                font-size: 0.8125rem;
                font-weight: 500;
                padding: 0.375rem 0.875rem;
                border-radius: 0.375rem;
                color: rgb(107 114 128);
                transition: background-color .15s, color .15s, box-shadow .15s;
            }
            .fi-hybrid-editor-field .ade-tab:hover { color: rgb(55 65 81); }
            .fi-hybrid-editor-field .ade-tab.is-active {
                background: rgb(255 255 255 / 0.9);
                color: rgb(17 24 39);
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.08);
            }
            .dark .fi-hybrid-editor-field .ade-tab.is-active {
                background: rgb(55 65 81);
                color: rgb(255 255 255);
            }
            .fi-hybrid-editor-field .ade-tab:focus-visible {
                outline: 2px solid rgb(var(--primary-600, 217 119 6));
                outline-offset: 2px;
            }
            .fi-hybrid-editor-field .ade-label {
                font-size: 0.6875rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: rgb(107 114 128);
                margin-bottom: 0.5rem;
            }
            .fi-hybrid-editor-field .ade-link-btn {
                font-size: 0.75rem;
                color: rgb(var(--primary-600, 217 119 6));
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
            }
            .fi-hybrid-editor-field .ade-link-btn:hover { text-decoration: underline; }
            .fi-hybrid-editor-field .ade-link-btn:focus-visible {
                outline: 2px solid rgb(var(--primary-600, 217 119 6));
                outline-offset: 2px;
            }
            .fi-hybrid-editor-field .ade-spinner {
                display: inline-block;
                width: 0.75rem;
                height: 0.75rem;
                border: 2px solid rgb(var(--primary-600, 217 119 6) / .3);
                border-top-color: rgb(var(--primary-600, 217 119 6));
                border-radius: 50%;
                animation: ade-spin .6s linear infinite;
            }
            @keyframes ade-spin { to { transform: rotate(360deg); } }
            .fi-hybrid-editor-field .ade-blocks-panel {
                border: 1px solid rgb(127 127 127 / 0.25);
                border-radius: 0.5rem;
                padding: 0.5rem;
                max-height: 500px;
                overflow-y: auto;
            }
            .fi-hybrid-editor-field .gjs-block {
                border-radius: 0.5rem !important;
                border: 1px solid rgb(127 127 127 / 0.25) !important;
                transition: border-color .15s, transform .1s !important;
            }
            .fi-hybrid-editor-field .gjs-block:hover {
                border-color: rgb(var(--primary-500, 245 158 11)) !important;
                transform: translateY(-1px);
            }
            .fi-hybrid-editor-field .gjs-block-category .gjs-title {
                font-weight: 600 !important;
                font-size: 0.8125rem !important;
            }
        </style>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('hybridEditor', ({ state, blocks, canWriteCode }) => {
                    const monacoEditors = {};
                    const monacoEditorEls = {};
                    let grapesEditor = null;
                    let previewTimeout = null;

                    return {
                        state,
                        canWriteCode,
                        mode: 'visual',
                        activeTab: 'html',
                        codeLayout: 'side',
                        monacoReady: false,
                        grapesReady: false,
                        loadError: null,
                        syncing: false,

                        init() {
                            if (document.readyState === 'complete') {
                                this.$nextTick(() => this.initGrapes());
                            } else {
                                window.addEventListener('load', () => {
                                    this.$nextTick(() => this.initGrapes());
                                }, { once: true });
                            }
                        },

                        setMode(newMode) {
                            if (!this.canWriteCode && newMode === 'code') return;
                            if (this.mode === newMode) return;

                            this.mode = newMode;

                            this.$nextTick(() => {
                                if (newMode === 'code') {
                                    if (!this.monacoReady) {
                                        this.initMonaco();
                                    } else {
                                        ['html', 'css', 'js'].forEach((key) => {
                                            monacoEditors[key]?.setValue((this.state && this.state[key]) || '');
                                        });
                                        this.refreshCodePreview();
                                    }
                                } else if (!this.grapesReady) {
                                    this.initGrapes();
                                } else {
                                    grapesEditor.setComponents((this.state && this.state.html) || '');
                                    grapesEditor.setStyle((this.state && this.state.css) || '');
                                    this.refreshVisualPreview();
                                }
                            });
                        },

                        // --- GrapesJS (mode visuel) ---

                        initGrapes() {
                            this.loadGrapesJs(() => {
                                try {
                                    this.createGrapesEditor();
                                    this.grapesReady = true;
                                    this.$nextTick(() => this.refreshVisualPreview());
                                } catch (e) {
                                    this.loadError = e.message;
                                }
                            });
                        },

                        loadGrapesJs(callback) {
                            if (window.grapesjs) {
                                callback();
                                return;
                            }
                            if (window.__grapesCallbacks) {
                                window.__grapesCallbacks.push(callback);
                                return;
                            }
                            window.__grapesCallbacks = [callback];

                            const cssLink = document.createElement('link');
                            cssLink.rel = 'stylesheet';
                            cssLink.href = 'https://cdn.jsdelivr.net/npm/grapesjs@0.21.7/dist/css/grapes.min.css';
                            document.head.appendChild(cssLink);

                            const script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/grapesjs@0.21.7/dist/grapes.min.js';
                            script.onerror = () => {
                                this.loadError = "Impossible de charger GrapesJS depuis le CDN.";
                            };
                            script.onload = () => {
                                window.__grapesCallbacks.forEach((cb) => cb());
                                window.__grapesCallbacks = null;
                            };
                            document.head.appendChild(script);
                        },

                        createGrapesEditor() {
                            grapesEditor = grapesjs.init({
                                container: this.$refs.canvas,
                                height: '500px',
                                fromElement: false,
                                storageManager: false,
                                blockManager: { appendTo: this.$refs.blocks },
                            });

                            if (this.state && this.state.html) {
                                grapesEditor.setComponents(this.state.html);
                            }
                            if (this.state && this.state.css) {
                                grapesEditor.setStyle(this.state.css);
                            }

                            blocks.forEach((block) => {
                                grapesEditor.BlockManager.add('atomic-' + block.id, {
                                    label: '🧩 ' + block.label,
                                    category: block.category,
                                    content: {
                                        tagName: 'div',
                                        attributes: { 'data-atomic-ref': block.id },
                                        content: '🧩 ' + block.label,
                                        style: {
                                            display: 'inline-block',
                                            padding: '8px 12px',
                                            border: '1px dashed #9ca3af',
                                            'border-radius': '4px',
                                            margin: '2px',
                                            background: '#f3f4f6',
                                            color: '#4b5563',
                                            'font-size': '12px',
                                        },
                                    },
                                });
                            });

                            [
                                { id: 'text', label: 'Texte', category: 'Générique', content: { tagName: 'div', content: 'Texte' } },
                                { id: 'image', label: 'Image', category: 'Générique', content: { type: 'image', attributes: { src: 'https://via.placeholder.com/150', alt: '' } } },
                                { id: 'link', label: 'Lien', category: 'Générique', content: { tagName: 'a', attributes: { href: '#' }, content: 'Lien' } },
                                {
                                    id: 'div',
                                    label: 'Bloc (div)',
                                    category: 'Générique',
                                    content: { tagName: 'div', style: { padding: '10px', 'min-height': '40px', border: '1px dashed #ccc' } },
                                },
                            ].forEach((block) => grapesEditor.BlockManager.add('generic-' + block.id, block));

                            // Plusieurs événements écoutés (pas seulement "update") : les changements
                            // via le panneau de style à droite n'émettaient pas toujours "update" seul.
                            grapesEditor.on('update component:update style:update component:add component:remove', () => {
                                this.debouncedSyncFromGrapes();
                            });
                        },

                        debouncedSyncFromGrapes() {
                            clearTimeout(previewTimeout);
                            previewTimeout = setTimeout(() => this.syncFromGrapes(), 500);
                        },

                        syncFromGrapes() {
                            const html = grapesEditor.getHtml();
                            const css = grapesEditor.getCss();
                            this.state = { ...this.state, html, css };
                            this.refreshVisualPreview();
                        },

                        // --- Monaco (mode code) ---

                        initMonaco() {
                            this.loadMonaco(() => {
                                try {
                                    this.createMonacoEditors();
                                    this.monacoReady = true;
                                    this.refreshCodePreview();
                                } catch (e) {
                                    this.loadError = e.message;
                                }
                            });
                        },

                        loadMonaco(callback) {
                            if (window.monaco) {
                                callback();
                                return;
                            }
                            if (window.__monacoCallbacks) {
                                window.__monacoCallbacks.push(callback);
                                return;
                            }
                            window.__monacoCallbacks = [callback];

                            const loaderScript = document.createElement('script');
                            loaderScript.src = 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js';
                            loaderScript.onerror = () => {
                                this.loadError = "Impossible de charger Monaco depuis le CDN.";
                            };
                            loaderScript.onload = () => {
                                window.require.config({
                                    paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' },
                                });
                                window.require(['vs/editor/editor.main'], () => {
                                    window.__monacoCallbacks.forEach((cb) => cb());
                                    window.__monacoCallbacks = null;
                                    delete window.define;
                                });
                            };
                            document.head.appendChild(loaderScript);
                        },

                        createMonacoEditors() {
                            const container = this.$refs.monacoContainer;
                            container.innerHTML = '';
                            const langs = { html: 'html', css: 'css', js: 'javascript' };

                            ['html', 'css', 'js'].forEach((key) => {
                                const el = document.createElement('div');
                                el.style.height = '100%';
                                el.style.display = key === this.activeTab ? 'block' : 'none';
                                container.appendChild(el);
                                monacoEditorEls[key] = el;

                                monacoEditors[key] = monaco.editor.create(el, {
                                    value: (this.state && this.state[key]) || '',
                                    language: langs[key],
                                    theme: 'vs-dark',
                                    automaticLayout: true,
                                    minimap: { enabled: false },
                                });

                                monacoEditors[key].onDidChangeModelContent(() => {
                                    this.state = { ...this.state, [key]: monacoEditors[key].getValue() };
                                    this.debouncedCodePreview();
                                });
                            });
                        },

                        switchTab(tab) {
                            this.activeTab = tab;
                            Object.keys(monacoEditorEls).forEach((key) => {
                                monacoEditorEls[key].style.display = key === tab ? 'block' : 'none';
                            });
                            monacoEditors[tab]?.layout();
                        },

                        debouncedCodePreview() {
                            clearTimeout(previewTimeout);
                            previewTimeout = setTimeout(() => this.refreshCodePreview(), 500);
                        },

                        // --- Aperçus résolus (partagent la même résolution serveur) ---

                        refreshCodePreview() {
                            this.resolveAndRender(this.$refs.codePreview);
                        },

                        refreshVisualPreview() {
                            this.resolveAndRender(this.$refs.visualPreview);
                        },

                        resolveAndRender(iframe) {
                            if (!iframe) return;

                            this.syncing = true;

                            this.$wire.call('resolvePreviewHtml', this.state).then((resolved) => {
                                iframe.srcdoc = this.buildPreviewHtml(resolved);
                                this.syncing = false;
                            }).catch(() => {
                                iframe.srcdoc = this.buildPreviewHtml(this.state);
                                this.syncing = false;
                            });
                        },

                        buildPreviewHtml(source) {
                            const doc = document.implementation.createHTMLDocument('');

                            const meta = doc.createElement('meta');
                            meta.setAttribute('http-equiv', 'Content-Security-Policy');
                            meta.setAttribute('content', "default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data:;");
                            doc.head.appendChild(meta);

                            const style = doc.createElement('style');
                            style.textContent = (source && source.css) || '';
                            doc.head.appendChild(style);

                            doc.body.innerHTML = (source && source.html) || '';

                            if (source && source.js) {
                                const scriptEl = doc.createElement('script');
                                scriptEl.textContent = source.js;
                                doc.body.appendChild(scriptEl);
                            }

                            return '\x3C!DOCTYPE html>' + doc.documentElement.outerHTML;
                        },
                    };
                });
            });
        </script>
    @endonce
</x-dynamic-component>
