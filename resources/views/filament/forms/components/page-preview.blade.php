<div x-data="pagePreview()" wire:ignore>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
        <p class="ade-label" style="margin: 0;">Page assemblée (composants résolus, placeholders remplacés)</p>
        <button type="button" @click="refresh()" class="ade-link-btn">
            <span x-show="syncing" class="ade-spinner"></span>
            <span x-text="syncing ? 'Actualisation…' : 'Actualiser'"></span>
        </button>
    </div>
    <iframe
        x-ref="preview"
        sandbox="allow-scripts"
        style="width: 100%; height: 500px; border: 1px solid rgb(127 127 127 / 0.25); border-radius: 0.5rem; background: white;"
    ></iframe>
</div>

@once
    <style>
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
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }
        .ade-link-btn:hover { text-decoration: underline; }
        .ade-spinner {
            display: inline-block;
            width: 0.75rem;
            height: 0.75rem;
            border: 2px solid rgb(var(--primary-600, 217 119 6) / .3);
            border-top-color: rgb(var(--primary-600, 217 119 6));
            border-radius: 50%;
            animation: ade-spin .6s linear infinite;
        }
        @keyframes ade-spin { to { transform: rotate(360deg); } }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pagePreview', () => ({
                syncing: false,

                init() {
                    if (document.readyState === 'complete') {
                        this.refresh();
                    } else {
                        window.addEventListener('load', () => this.refresh(), { once: true });
                    }
                },

                refresh() {
                    this.syncing = true;

                    this.$wire.call('renderPagePreview').then((resolved) => {
                        this.$refs.preview.srcdoc = this.buildDoc(resolved);
                        this.syncing = false;
                    }).catch(() => {
                        this.syncing = false;
                    });
                },

                buildDoc(source) {
                    const doc = document.implementation.createHTMLDocument('');

                    const meta = doc.createElement('meta');
                    meta.setAttribute('http-equiv', 'Content-Security-Policy');
                    meta.setAttribute('content', "default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data:;");
                    doc.head.appendChild(meta);

                    const style = doc.createElement('style');
                    style.textContent = (source && source.css) || '';
                    doc.head.appendChild(style);

                    doc.body.innerHTML = (source && source.html) || '';

                    return '\x3C!DOCTYPE html>' + doc.documentElement.outerHTML;
                },
            }));
        });
    </script>
@endonce
