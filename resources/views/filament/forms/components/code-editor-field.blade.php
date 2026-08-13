<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="codeEditor({ state: $wire.entangle('{{ $getStatePath() }}') })"
        class="fi-code-editor-field"
    >
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" @click="switchTab('html')" :style="activeTab === 'html' ? 'font-weight: bold; text-decoration: underline;' : ''" style="font-size: 0.875rem;">HTML</button>
                <button type="button" @click="switchTab('css')" :style="activeTab === 'css' ? 'font-weight: bold; text-decoration: underline;' : ''" style="font-size: 0.875rem;">CSS</button>
                <button type="button" @click="switchTab('js')" :style="activeTab === 'js' ? 'font-weight: bold; text-decoration: underline;' : ''" style="font-size: 0.875rem;">JS</button>
            </div>

            <button
                type="button"
                @click="layout = (layout === 'side' ? 'stacked' : 'side')"
                style="font-size: 0.75rem; color: #6b7280; text-decoration: underline;"
            >
                <span x-text="layout === 'side' ? 'Aperçu en dessous' : 'Aperçu à droite'"></span>
            </button>
        </div>

        <div :style="layout === 'side' ? 'display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;' : 'display: grid; grid-template-columns: 1fr; gap: 1rem;'">
            <div x-ref="editorContainer" style="height: 420px; border: 1px solid #d1d5db; border-radius: 0.5rem; overflow: hidden; position: relative;">
                <p x-show="!monacoReady && !loadError" style="padding: 1rem; color: #6b7280; font-size: 0.875rem;">Chargement de l'éditeur…</p>
                <p x-show="loadError" x-text="'Erreur : ' + loadError" style="padding: 1rem; color: #f87171; font-size: 0.875rem;"></p>
            </div>

            <div>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Aperçu résolu (sandbox — les références vivantes vers d'autres composants sont remplacées par leur contenu publié actuel, actualisé ~600ms après la dernière frappe)
                </p>
                <iframe
                    x-ref="preview"
                    sandbox="allow-scripts"
                    style="width: 100%; height: 420px; border: 1px solid #d1d5db; border-radius: 0.5rem; background: white;"
                ></iframe>
            </div>
        </div>
    </div>

    @once
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('codeEditor', ({ state }) => {
                    const editors = {};
                    const editorEls = {};
                    let previewTimeout = null;

                    return {
                        state,
                        activeTab: 'html',
                        layout: 'side',
                        monacoReady: false,
                        loadError: null,

                        init() {
                            try {
                                if (document.readyState === 'complete') {
                                    this.loadMonaco(() => this.onMonacoReady());
                                } else {
                                    window.addEventListener('load', () => {
                                        this.loadMonaco(() => this.onMonacoReady());
                                    }, { once: true });
                                }
                            } catch (e) {
                                this.loadError = e.message;
                            }
                        },

                        onMonacoReady() {
                            try {
                                this.monacoReady = true;
                                this.createEditors();
                                this.updatePreview();
                            } catch (e) {
                                this.loadError = e.message;
                            }
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
                                this.loadError = "Impossible de charger Monaco depuis le CDN (jsdelivr). Vérifiez la connexion ou une extension qui bloquerait le script.";
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

                        createEditors() {
                            const container = this.$refs.editorContainer;
                            container.innerHTML = '';
                            const langs = { html: 'html', css: 'css', js: 'javascript' };

                            ['html', 'css', 'js'].forEach((key) => {
                                const el = document.createElement('div');
                                el.style.height = '100%';
                                el.style.display = key === this.activeTab ? 'block' : 'none';
                                container.appendChild(el);
                                editorEls[key] = el;

                                editors[key] = monaco.editor.create(el, {
                                    value: (this.state && this.state[key]) || '',
                                    language: langs[key],
                                    theme: 'vs-dark',
                                    automaticLayout: true,
                                    minimap: { enabled: false },
                                });

                                editors[key].onDidChangeModelContent(() => {
                                    this.state = { ...this.state, [key]: editors[key].getValue() };
                                    this.debouncedPreview();
                                });
                            });
                        },

                        switchTab(tab) {
                            this.activeTab = tab;
                            Object.keys(editorEls).forEach((key) => {
                                editorEls[key].style.display = key === tab ? 'block' : 'none';
                            });
                            editors[tab]?.layout();
                        },

                        debouncedPreview() {
                            clearTimeout(previewTimeout);
                            previewTimeout = setTimeout(() => this.updatePreview(), 600);
                        },

                        // Passe désormais par la résolution serveur (Module 4) pour rester
                        // cohérent avec le WYSIWYG : un data-atomic-ref tapé à la main en
                        // mode code est résolu de la même façon.
                        updatePreview() {
                            this.$wire.call('resolvePreviewHtml', this.state).then((resolved) => {
                                this.$refs.preview.srcdoc = this.buildPreviewHtml(resolved);
                            }).catch(() => {
                                this.$refs.preview.srcdoc = this.buildPreviewHtml(this.state);
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

                            const scriptEl = doc.createElement('script');
                            scriptEl.textContent = (source && source.js) || '';
                            doc.body.appendChild(scriptEl);

                            return '\x3C!DOCTYPE html>' + doc.documentElement.outerHTML;
                        },
                    };
                });
            });
        </script>
    @endonce
</x-dynamic-component>
