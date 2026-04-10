<!-- ══════════════════════════════════════════════════════════════════ -->
<!-- SWITCH (TOGGLE) -->
<!-- ══════════════════════════════════════════════════════════════════ -->
<section class="ks-section" id="ks-switch-component">
    <h2 class="ks-section-title">Switch / Toggle</h2>

    <div class="ks-tabs" role="tablist">
        <button class="ks-tab is-active" role="tab" aria-selected="true" data-tab="switch-nolabel">Kein Label</button>
        <button class="ks-tab" role="tab" aria-selected="false" data-tab="switch-left">Label links</button>
        <button class="ks-tab" role="tab" aria-selected="false" data-tab="switch-right">Label rechts</button>
    </div>

    <!-- Tab 1: Kein Label -->
    <div class="ks-tab-panel is-active" data-panel="switch-nolabel">
        <div class="ks-cb-states">
            <!-- Off states -->
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Disabled</div>
                <label class="ks-switch" aria-label="Off disabled" style="pointer-events:none">
                    <input type="checkbox" disabled tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Off</div>
                <label class="ks-switch" aria-label="Off" style="pointer-events:none">
                    <input type="checkbox" tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <div class="ks-btn-state__label">Focus</div>
                <label class="ks-switch ks-switch--focus" aria-label="Off focus" style="pointer-events:none">
                    <input type="checkbox" tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <!-- On states -->
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Disabled</div>
                <label class="ks-switch" aria-label="On disabled" style="pointer-events:none">
                    <input type="checkbox" checked disabled tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">On</div>
                <label class="ks-switch" aria-label="On" style="pointer-events:none">
                    <input type="checkbox" checked tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <div class="ks-cb-state ks-cb-state--focus">
                <div class="ks-btn-state__label">Focus</div>
                <label class="ks-switch ks-switch--focus" aria-label="On focus" style="pointer-events:none">
                    <input type="checkbox" checked tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
        </div>
    </div><!-- /switch-nolabel -->

    <!-- Tab 2: Label links -->
    <div class="ks-tab-panel" data-panel="switch-left">
        <div class="ks-cb-states ks-cb-states--labeled">
            <!-- Off states -->
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Disabled</div>
                <label class="ks-switch ks-switch--label-left" style="pointer-events:none">
                    <span class="ks-cb-text">Label</span>
                    <input type="checkbox" disabled tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Off</div>
                <label class="ks-switch ks-switch--label-left" style="pointer-events:none">
                    <span class="ks-cb-text">Label</span>
                    <input type="checkbox" tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Focus</div>
                <label class="ks-switch ks-switch--label-left ks-switch--focus" style="pointer-events:none">
                    <span class="ks-cb-text">Label</span>
                    <input type="checkbox" tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <!-- On states -->
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Disabled</div>
                <label class="ks-switch ks-switch--label-left" style="pointer-events:none">
                    <span class="ks-cb-text">Label</span>
                    <input type="checkbox" checked disabled tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">On</div>
                <label class="ks-switch ks-switch--label-left" style="pointer-events:none">
                    <span class="ks-cb-text">Label</span>
                    <input type="checkbox" checked tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Focus</div>
                <label class="ks-switch ks-switch--label-left ks-switch--focus" style="pointer-events:none">
                    <span class="ks-cb-text">Label</span>
                    <input type="checkbox" checked tabindex="-1">
                    <span class="ks-switch__track"></span>
                </label>
            </div>
        </div>
    </div><!-- /switch-left -->

    <!-- Tab 3: Label rechts -->
    <div class="ks-tab-panel" data-panel="switch-right">
        <div class="ks-cb-states ks-cb-states--labeled">
            <!-- Off states -->
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Disabled</div>
                <label class="ks-switch ks-switch--label-right" style="pointer-events:none">
                    <input type="checkbox" disabled tabindex="-1">
                    <span class="ks-switch__track"></span>
                    <span class="ks-cb-text">Label</span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Off</div>
                <label class="ks-switch ks-switch--label-right" style="pointer-events:none">
                    <input type="checkbox" tabindex="-1">
                    <span class="ks-switch__track"></span>
                    <span class="ks-cb-text">Label</span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Focus</div>
                <label class="ks-switch ks-switch--label-right ks-switch--focus" style="pointer-events:none">
                    <input type="checkbox" tabindex="-1">
                    <span class="ks-switch__track"></span>
                    <span class="ks-cb-text">Label</span>
                </label>
            </div>
            <!-- On states -->
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Disabled</div>
                <label class="ks-switch ks-switch--label-right" style="pointer-events:none">
                    <input type="checkbox" checked disabled tabindex="-1">
                    <span class="ks-switch__track"></span>
                    <span class="ks-cb-text">Label</span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">On</div>
                <label class="ks-switch ks-switch--label-right" style="pointer-events:none">
                    <input type="checkbox" checked tabindex="-1">
                    <span class="ks-switch__track"></span>
                    <span class="ks-cb-text">Label</span>
                </label>
            </div>
            <div class="ks-cb-state">
                <div class="ks-btn-state__label">Focus</div>
                <label class="ks-switch ks-switch--label-right ks-switch--focus" style="pointer-events:none">
                    <input type="checkbox" checked tabindex="-1">
                    <span class="ks-switch__track"></span>
                    <span class="ks-cb-text">Label</span>
                </label>
            </div>
        </div>
    </div><!-- /switch-right -->

</section>
