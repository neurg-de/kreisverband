<!-- ══════════════════════════════════════════════════════════════════ -->
<!-- CHECKBOX -->
<!-- ══════════════════════════════════════════════════════════════════ -->
<section class="ks-section" id="ks-checkbox">
    <h2 class="ks-section-title">Checkbox</h2>

    <div class="ks-typo-intro">
        <p>Wir verwenden einheitliche Stile für alle Checkboxes in unseren digitalen Produkten.</p>
    </div>

    <div class="ks-tabs" role="tablist">
        <button class="ks-tab is-active" role="tab" aria-selected="true"  data-tab="cb-nolabel">No label</button>
        <button class="ks-tab"            role="tab" aria-selected="false" data-tab="cb-left">Label left</button>
        <button class="ks-tab"            role="tab" aria-selected="false" data-tab="cb-right">Label right</button>
    </div>

    <!-- Tab: No label -->
    <div class="ks-tab-panel is-active" data-panel="cb-nolabel">
        <p class="ks-btn-specs">Size: 24×24px · Focus: 30×30px</p>

        <!-- Behaviour: Off -->
        <h3 class="ks-label">Behaviour: Off</h3>
        <div class="ks-cb-states">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <input type="checkbox" disabled aria-disabled="true" tabindex="-1">
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <input type="checkbox" tabindex="-1">
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <input type="checkbox" tabindex="-1">
            </div>
        </div>

        <!-- Behaviour: On -->
        <h3 class="ks-label">Behaviour: On</h3>
        <div class="ks-cb-states">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <input type="checkbox" checked disabled aria-disabled="true" tabindex="-1">
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <input type="checkbox" checked tabindex="-1">
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <input type="checkbox" checked tabindex="-1">
            </div>
        </div>
    </div>

    <!-- Tab: Label left -->
    <div class="ks-tab-panel" data-panel="cb-left">
        <p class="ks-btn-specs">Width: 328px · Height: ~22.5px · Focus: ~28px</p>

        <!-- Behaviour: Off -->
        <h3 class="ks-label">Behaviour: Off</h3>
        <div class="ks-cb-states ks-cb-states--labeled">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Checkbox label</span>
                    <input type="checkbox" disabled aria-disabled="true" tabindex="-1">
                </label>
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Checkbox label</span>
                    <input type="checkbox" tabindex="-1">
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Checkbox label</span>
                    <input type="checkbox" tabindex="-1">
                </label>
            </div>
        </div>

        <!-- Behaviour: On -->
        <h3 class="ks-label">Behaviour: On</h3>
        <div class="ks-cb-states ks-cb-states--labeled">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Checkbox label</span>
                    <input type="checkbox" checked disabled aria-disabled="true" tabindex="-1">
                </label>
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Checkbox label</span>
                    <input type="checkbox" checked tabindex="-1">
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <label class="ks-cb-label-left">
                    <span class="ks-cb-text">Checkbox label</span>
                    <input type="checkbox" checked tabindex="-1">
                </label>
            </div>
        </div>
    </div>

    <!-- Tab: Label right -->
    <div class="ks-tab-panel" data-panel="cb-right">
        <p class="ks-btn-specs">Width: 328px · Height: ~22.5px · Focus: ~28px</p>

        <!-- Behaviour: Off -->
        <h3 class="ks-label">Behaviour: Off</h3>
        <div class="ks-cb-states ks-cb-states--labeled">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <label class="ks-cb-label-right">
                    <input type="checkbox" disabled aria-disabled="true" tabindex="-1">
                    <span class="ks-cb-text">Checkbox label</span>
                </label>
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <label class="ks-cb-label-right">
                    <input type="checkbox" tabindex="-1">
                    <span class="ks-cb-text">Checkbox label</span>
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <label class="ks-cb-label-right">
                    <input type="checkbox" tabindex="-1">
                    <span class="ks-cb-text">Checkbox label</span>
                </label>
            </div>
        </div>

        <!-- Behaviour: On -->
        <h3 class="ks-label">Behaviour: On</h3>
        <div class="ks-cb-states ks-cb-states--labeled">
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Disabled</span>
                <label class="ks-cb-label-right">
                    <input type="checkbox" checked disabled aria-disabled="true" tabindex="-1">
                    <span class="ks-cb-text">Checkbox label</span>
                </label>
            </div>
            <div class="ks-cb-state">
                <span class="ks-btn-state__label">Enabled</span>
                <label class="ks-cb-label-right">
                    <input type="checkbox" checked tabindex="-1">
                    <span class="ks-cb-text">Checkbox label</span>
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <span class="ks-btn-state__label">Focus</span>
                <label class="ks-cb-label-right">
                    <input type="checkbox" checked tabindex="-1">
                    <span class="ks-cb-text">Checkbox label</span>
                </label>
            </div>
        </div>
    </div>

</section>
