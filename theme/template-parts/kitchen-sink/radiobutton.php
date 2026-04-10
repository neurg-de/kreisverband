<!-- ══════════════════════════════════════════════════════════════════ -->
<!-- RADIOBUTTON -->
<!-- ══════════════════════════════════════════════════════════════════ -->
<section class="ks-section" id="ks-radiobutton">
    <h2 class="ks-section-title">Radiobutton</h2>

    <div class="ks-typo-intro">
        <p>Wir verwenden einheitliche Stile für alle Radiobuttons in unseren digitalen Produkten.</p>
    </div>

    <div class="ks-tabs" role="tablist">
        <button class="ks-tab is-active" role="tab" aria-selected="true"  data-tab="rb-nolabel">Ohne Label</button>
        <button class="ks-tab"            role="tab" aria-selected="false" data-tab="rb-left">Label links</button>
        <button class="ks-tab"            role="tab" aria-selected="false" data-tab="rb-right">Label rechts</button>
    </div>

    <!-- Tab: Ohne Label -->
    <div class="ks-tab-panel is-active" data-panel="rb-nolabel">
        <p class="ks-btn-specs">Size: 24×24px · Focus: 30×30px</p>

        <!-- Behaviour: Off -->
        <h3 class="ks-label">Behaviour: Off</h3>
        <div class="ks-cb-states">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <input type="radio" disabled aria-disabled="true" tabindex="-1">
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <input type="radio" tabindex="-1">
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <input type="radio" tabindex="-1">
            </div>
        </div>

        <!-- Behaviour: On -->
        <h3 class="ks-label">Behaviour: On</h3>
        <div class="ks-cb-states">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <input type="radio" checked disabled aria-disabled="true" tabindex="-1">
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <input type="radio" checked tabindex="-1">
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <input type="radio" checked tabindex="-1">
            </div>
        </div>
    </div>

    <!-- Tab: Label links -->
    <div class="ks-tab-panel" data-panel="rb-left">
        <p class="ks-btn-specs">Width: 328px · Height: ~22.5px · Focus: ~28px</p>

        <!-- Behaviour: Off -->
        <h3 class="ks-label">Behaviour: Off</h3>
        <div class="ks-cb-states ks-cb-states--labeled">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Radiobutton label</span>
                    <input type="radio" disabled aria-disabled="true" tabindex="-1">
                </label>
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Radiobutton label</span>
                    <input type="radio" tabindex="-1">
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Radiobutton label</span>
                    <input type="radio" tabindex="-1">
                </label>
            </div>
        </div>

        <!-- Behaviour: On -->
        <h3 class="ks-label">Behaviour: On</h3>
        <div class="ks-cb-states ks-cb-states--labeled">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Radiobutton label</span>
                    <input type="radio" checked disabled aria-disabled="true" tabindex="-1">
                </label>
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Radiobutton label</span>
                    <input type="radio" checked tabindex="-1">
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Radiobutton label</span>
                    <input type="radio" checked tabindex="-1">
                </label>
            </div>
        </div>
    </div>

    <!-- Tab: Label rechts -->
    <div class="ks-tab-panel" data-panel="rb-right">
        <p class="ks-btn-specs">Width: 328px · Height: ~22.5px · Focus: ~28px</p>

        <!-- Behaviour: Off -->
        <h3 class="ks-label">Behaviour: Off</h3>
        <div class="ks-cb-states ks-cb-states--labeled">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <label class="ks-cb-label-right">
                    <input type="radio" disabled aria-disabled="true" tabindex="-1">
                    <span class="ks-cb-text">Radiobutton label</span>
                </label>
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <label class="ks-cb-label-right">
                    <input type="radio" tabindex="-1">
                    <span class="ks-cb-text">Radiobutton label</span>
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <label class="ks-cb-label-right">
                    <input type="radio" tabindex="-1">
                    <span class="ks-cb-text">Radiobutton label</span>
                </label>
            </div>
        </div>

        <!-- Behaviour: On -->
        <h3 class="ks-label">Behaviour: On</h3>
        <div class="ks-cb-states ks-cb-states--labeled">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <label class="ks-cb-label-right">
                    <input type="radio" checked disabled aria-disabled="true" tabindex="-1">
                    <span class="ks-cb-text">Radiobutton label</span>
                </label>
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <label class="ks-cb-label-right">
                    <input type="radio" checked tabindex="-1">
                    <span class="ks-cb-text">Radiobutton label</span>
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <label class="ks-cb-label-right">
                    <input type="radio" checked tabindex="-1">
                    <span class="ks-cb-text">Radiobutton label</span>
                </label>
            </div>
        </div>
    </div>

</section>
