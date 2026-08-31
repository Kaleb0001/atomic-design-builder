<div x-data="pagePreview({ result: $wire.entangle('previewResult') })" wire:ignore>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
        <div class="ade-tabs">
            <button type="button" @click="view = 'visual'" class="ade-tab" :class="view === 'visual' && 'is-active'">Visuel</button>
            <button type="button" @click="view = 'code'" class="ade-tab" :class="view === 'code' && 'is-active'">Code</button>
        </div>
        <button type="button" @click="$wire.call('refreshPreview')" class="ade-link-btn">Actualiser</button>
    </div>

    <div x-show="view === 'visual'">
        <iframe
            x-ref="preview"
            sandbox="allow-scripts"
            style="width: 100%; height: 500px; border: 1px solid rgb(127 127 127 / 0.25); border-radius: 0.5rem; background: white;"
        ></iframe>
    </div>

    <div x-show="view === 'code'">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
            <p class="ade-label" style="margin: 0;">HTML</p>
            <button type="button" @click="copyToClipboard((result && result.html) || '')" class="ade-link-btn">Copier</button>
        </div>
        <pre class="ade-code-block" x-text="(result && result.html) || ''"></pre>

        <div style="display: flex; align-items: center; justify-content: space-between; margin: 0.75rem 0 0.25rem;">
            <p class="ade-label" style="margin: 0;">CSS</p>
            <button type="button" @click="copyToClipboard((result && result.css) || '')" class="ade-link-btn">Copier</button>
        </div>
        <pre class="ade-code-block" x-text="(result && result.css) || ''"></pre>
    </div>
</div>

@once
    <style>
        .ade-tabs {
            display: inline-flex;
            background: rgb(127 127 127 / 0.1);
            border-radius: 0.5rem;
            padding: 3px;
            gap: 2px;
        }
        .ade-tab {
            font-size: 0.8125rem;
            font-weight: 500;
            padding: 0.375rem 0.875rem;
            border-radius: 0.375rem;
            color: rgb(107 114 128);
            transition: background-color .15s, color .15s, box-shadow .15s;
        }
        .ade-tab:hover { color: rgb(55 65 81); }
        .ade-tab.is-active {
            background: rgb(255 255 255 / 0.9);
            color: rgb(17 24 39);
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.08);
        }
        .dark .ade-tab.is-active {
            background: rgb(55 65 81);
            color: rgb(255 255 255);
        }
        .ade-tab:focus-visible, .ade-link-btn:focus-visible {
            outline: 2px solid rgb(var(--primary-600, 217 119 6));
            outline-offset: 2px;
        }
        .ade-label {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgb(107 114 128);
        }
        .ade-link-btn {
            font-size: 0.75rem;
            color: rgb(var(--primary-600, 217 119 6));
        }
        .ade-link-btn:hover { text-decoration: underline; }
        .ade-code-block {
            background: rgb(127 127 127 / 0.08);
            padding: 0.75rem;
            border-radius: 0.5rem;
            overflow: auto;
            max-height: 260px;
            font-size: 0.75rem;
            white-space: pre-wrap;
            word-break: break-word;
            margin: 0;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pagePreview', ({ result }) => ({
                result,
                view: 'visual',

                init() {
                    this.render();
                    // Se redéclenche automatiquement à chaque changement du formulaire
                    // (le Repeater et ses champs sont "live" côté PHP).
                    this.$watch('result', () => this.render());
                },

                render() {
                    if (!this.$refs.preview) return;

                    const doc = document.implementation.createHTMLDocument('');

                    const meta = doc.createElement('meta');
                    meta.setAttribute('http-equiv', 'Content-Security-Policy');
                    meta.setAttribute('content', "default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data:;");
                    doc.head.appendChild(meta);

                    const style = doc.createElement('style');
                    style.textContent = (this.result && this.result.css) || '';
                    doc.head.appendChild(style);

                    doc.body.innerHTML = (this.result && this.result.html) || '';

                    this.$refs.preview.srcdoc = '\x3C!DOCTYPE html>' + doc.documentElement.outerHTML;
                },

                copyToClipboard(text) {
                    if (!text || !navigator.clipboard) return;
                    navigator.clipboard.writeText(text);
                },
            }));
        });
    </script>
@endonce
