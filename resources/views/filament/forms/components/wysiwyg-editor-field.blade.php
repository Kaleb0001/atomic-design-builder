<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="wysiwygEditor({ state: $wire.entangle('{{ $getStatePath() }}'), blocks: @js($getBlocks()) })"
        class="fi-wysiwyg-editor-field"
    >
        <p x-show="!ready && !loadError" style="padding: 1rem; color: #6b7280; font-size: 0.875rem;">Chargement de l'éditeur…</p>
        <p x-show="loadError" x-text="'Erreur : ' + loadError" style="padding: 1rem; color: #f87171; font-size: 0.875rem;"></p>

        <div x-show="ready" style="display: grid; grid-template-columns: 220px 1fr; gap: 1rem;">
            <div>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">Blocs</p>
                <div x-ref="blocks" style="border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 0.5rem; max-height: 500px; overflow-y: auto;"></div>
            </div>
            <div>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                    Canevas — les blocs 🧩 représentent des composants de la bibliothèque (référence vivante, résolue dans l'aperçu ci-dessous)
                </p>
                <div x-ref="canvas"></div>
            </div>
        </div>

        <div style="margin-top: 1rem;">
            <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem;">
                Aperçu résolu (sandbox — les références sont remplacées par le contenu publié actuel des composants, actualisé ~1s après une modification)
            </p>
            <iframe
                x-ref="preview"
                sandbox="allow-scripts"
                style="width: 100%; height: 300px; border: 1px solid #d1d5db; border-radius: 0.5rem; background: white;"
            ></iframe>
        </div>
    </div>

    @once
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('wysiwygEditor', ({ state, blocks }) => {
                    // Volontairement en dehors de l'objet réactif retourné : même raison
                    // que pour l'éditeur de code (Module 3) — une instance d'éditeur tiers
                    // complexe ne doit jamais être proxifiée par Alpine.
                    let grapesEditor = null;
                    let previewTimeout = null;

                    return {
                        state,
                        ready: false,
                        loadError: null,

                        init() {
                            try {
                                if (document.readyState === 'complete') {
                                    this.loadGrapesJs(() => this.onReady());
                                } else {
                                    window.addEventListener('load', () => {
                                        this.loadGrapesJs(() => this.onReady());
                                    }, { once: true });
                                }
                            } catch (e) {
                                this.loadError = e.message;
                            }
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
                                this.loadError = "Impossible de charger GrapesJS depuis le CDN (jsdelivr). Vérifiez la connexion ou une extension qui bloquerait le script.";
                            };
                            script.onload = () => {
                                window.__grapesCallbacks.forEach((cb) => cb());
                                window.__grapesCallbacks = null;
                            };
                            document.head.appendChild(script);
                        },

                        onReady() {
                            try {
                                this.ready = true;
                                this.$nextTick(() => this.createEditor());
                            } catch (e) {
                                this.loadError = e.message;
                            }
                        },

                        createEditor() {
                            try {
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

                                // Blocs "référence vivante" : un par composant publié de la bibliothèque.
                                blocks.forEach((block) => {
                                    grapesEditor.BlockManager.add('atomic-' + block.id, {
                                        label: '🧩 ' + block.label,
                                        category: block.category,
                                        content: '<div data-atomic-ref="' + block.id + '" '
                                            + 'style="display:inline-block;padding:8px 12px;border:1px dashed #9ca3af;'
                                            + 'border-radius:4px;margin:2px;background:#f3f4f6;color:#4b5563;font-size:12px;">'
                                            + '🧩 ' + block.label + '</div>',
                                    });
                                });

                                // Blocs génériques de base.
                                [
                                    { id: 'text', label: 'Texte', category: 'Générique', content: '<div>Texte</div>' },
                                    { id: 'image', label: 'Image', category: 'Générique', content: '<img src="https://via.placeholder.com/150" alt="" />' },
                                    { id: 'link', label: 'Lien', category: 'Générique', content: '<a href="#">Lien</a>' },
                                    { id: 'div', label: 'Bloc (div)', category: 'Générique', content: '<div style="padding:10px;min-height:40px;border:1px dashed #ccc;"></div>' },
                                ].forEach((block) => grapesEditor.BlockManager.add('generic-' + block.id, block));

                                grapesEditor.on('update', () => this.debouncedSync());

                                this.syncState();
                            } catch (e) {
                                this.loadError = e.message;
                            }
                        },

                        debouncedSync() {
                            clearTimeout(previewTimeout);
                            previewTimeout = setTimeout(() => this.syncState(), 1000);
                        },

                        syncState() {
                            const html = grapesEditor.getHtml();
                            const css = grapesEditor.getCss();

                            // Le champ js n'est pas édité en mode WYSIWYG : on le préserve tel quel.
                            this.state = { ...this.state, html, css };

                            this.refreshPreview();
                        },

                        refreshPreview() {
                            this.$wire.call('resolvePreviewHtml', this.state).then((resolved) => {
                                const doc = document.implementation.createHTMLDocument('');

                                const meta = doc.createElement('meta');
                                meta.setAttribute('http-equiv', 'Content-Security-Policy');
                                meta.setAttribute('content', "default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data:;");
                                doc.head.appendChild(meta);

                                const style = doc.createElement('style');
                                style.textContent = resolved.css || '';
                                doc.head.appendChild(style);

                                doc.body.innerHTML = resolved.html || '';

                                if (resolved.js) {
                                    const scriptEl = doc.createElement('script');
                                    scriptEl.textContent = resolved.js;
                                    doc.body.appendChild(scriptEl);
                                }

                                this.$refs.preview.srcdoc = '\x3C!DOCTYPE html>' + doc.documentElement.outerHTML;
                            }).catch((e) => {
                                this.loadError = 'Aperçu : ' + (e?.message || 'échec de la résolution côté serveur');
                            });
                        },
                    };
                });
            });
        </script>
    @endonce
</x-dynamic-component>
