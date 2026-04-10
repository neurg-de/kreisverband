<!-- ══════════════════════════════════════════════════════════════════ -->
<!-- INPUT -->
<!-- ══════════════════════════════════════════════════════════════════ -->
<section class="ks-section" id="ks-input">
    <h2 class="ks-section-title">Input</h2>

    <div class="ks-typo-intro">
        <p>Wir verwenden einheitliche Stile für alle Eingabefelder in unseren digitalen Produkten.</p>
    </div>

    <div class="ks-tabs" role="tablist">
        <button class="ks-tab is-active" role="tab" aria-selected="true"  data-tab="input-only">Input only</button>
        <button class="ks-tab"            role="tab" aria-selected="false" data-tab="input-label">Input mit Label</button>
        <button class="ks-tab"            role="tab" aria-selected="false" data-tab="input-label-helper">Input mit Label und Helpertext</button>
    </div>

    <!-- Tab: Input only -->
    <div class="ks-tab-panel is-active" data-panel="input-only">
        <p class="ks-btn-specs">Width: 328px · Height: ~34.5px</p>
        <div class="ks-input-states">
            <div class="ks-input-state">
                <span class="ks-btn-state__label">Default</span>
                <div class="ks-input-wrap"><input type="text" placeholder="Platzhalter" tabindex="-1"></div>
            </div>
            <div class="ks-input-state">
                <span class="ks-btn-state__label">Disabled</span>
                <div class="ks-input-wrap"><input type="text" placeholder="Platzhalter" disabled tabindex="-1"></div>
            </div>
            <div class="ks-input-state ks-input-state--hover">
                <span class="ks-btn-state__label">Hover</span>
                <div class="ks-input-wrap"><input type="text" placeholder="Platzhalter" tabindex="-1"></div>
            </div>
            <div class="ks-input-state ks-input-state--active">
                <span class="ks-btn-state__label">Active</span>
                <div class="ks-input-wrap"><input type="text" placeholder="Platzhalter" tabindex="-1"></div>
            </div>
            <div class="ks-input-state ks-input-state--active">
                <span class="ks-btn-state__label">Typing</span>
                <div class="ks-input-wrap"><input type="text" value="Eingegebener Text" tabindex="-1"></div>
            </div>
        </div>
    </div>

    <!-- Tab: Input mit Label -->
    <div class="ks-tab-panel" data-panel="input-label">
        <p class="ks-btn-specs">Width: 328px · Height: ~60px</p>
        <div class="ks-input-states">
            <div class="ks-input-state">
                <span class="ks-btn-state__label">Default</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" placeholder="Platzhalter" tabindex="-1">
                </div>
            </div>
            <div class="ks-input-state">
                <span class="ks-btn-state__label">Disabled</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" placeholder="Platzhalter" disabled tabindex="-1">
                </div>
            </div>
            <div class="ks-input-state ks-input-state--hover">
                <span class="ks-btn-state__label">Hover</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" placeholder="Platzhalter" tabindex="-1">
                </div>
            </div>
            <div class="ks-input-state ks-input-state--active">
                <span class="ks-btn-state__label">Active</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" placeholder="Platzhalter" tabindex="-1">
                </div>
            </div>
            <div class="ks-input-state ks-input-state--active">
                <span class="ks-btn-state__label">Typing</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" value="Eingegebener Text" tabindex="-1">
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Input mit Label und Helpertext -->
    <div class="ks-tab-panel" data-panel="input-label-helper">
        <p class="ks-btn-specs">Width: 328px · Height: ~86px</p>
        <div class="ks-input-states">
            <div class="ks-input-state">
                <span class="ks-btn-state__label">Default</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" placeholder="Platzhalter" tabindex="-1">
                    <span class="form-helper">Hilfetext für dieses Feld</span>
                </div>
            </div>
            <div class="ks-input-state">
                <span class="ks-btn-state__label">Disabled</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" placeholder="Platzhalter" disabled tabindex="-1">
                    <span class="form-helper">Hilfetext für dieses Feld</span>
                </div>
            </div>
            <div class="ks-input-state ks-input-state--hover">
                <span class="ks-btn-state__label">Hover</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" placeholder="Platzhalter" tabindex="-1">
                    <span class="form-helper">Hilfetext für dieses Feld</span>
                </div>
            </div>
            <div class="ks-input-state ks-input-state--active">
                <span class="ks-btn-state__label">Active</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" placeholder="Platzhalter" tabindex="-1">
                    <span class="form-helper">Hilfetext für dieses Feld</span>
                </div>
            </div>
            <div class="ks-input-state ks-input-state--active">
                <span class="ks-btn-state__label">Typing</span>
                <div class="ks-input-wrap ks-input-wrap--labeled">
                    <label>Feldbezeichnung</label>
                    <input type="text" value="Eingegebener Text" tabindex="-1">
                    <span class="form-helper">Hilfetext für dieses Feld</span>
                </div>
            </div>
        </div>
    </div>

</section>
