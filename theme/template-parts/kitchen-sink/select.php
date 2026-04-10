<!-- ══════════════════════════════════════════════════════════════════ -->
<!-- SELECT (DROPDOWN) -->
<!-- ══════════════════════════════════════════════════════════════════ -->
<section class="ks-section" id="ks-select-component">
    <h2 class="ks-section-title">Select / Dropdown</h2>

    <div class="ks-tabs" role="tablist">
        <button class="ks-tab is-active" role="tab" aria-selected="true" data-tab="select-einsp">Dropdown einspaltig</button>
        <button class="ks-tab" role="tab" aria-selected="false" data-tab="select-mehrsp">Dropdown mehrspaltig</button>
        <button class="ks-tab" role="tab" aria-selected="false" data-tab="select-items">List Items</button>
    </div>

    <!-- Tab 1: Dropdown einspaltig -->
    <div class="ks-tab-panel is-active" data-panel="select-einsp">

        <!-- Group 1: Text only -->
        <h3 class="ks-label">Text only</h3>
        <div class="ks-select-group">
            <!-- Collapsed trigger -->
            <div class="ks-select-item-wrap">
                <div class="ks-btn-state__label">Collapsed</div>
                <div class="ks-select-trigger">
                    <span class="ks-select-trigger__text">Dropdown</span>
                    <svg class="ks-select-trigger__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10l5 5 5-5z"/></svg>
                </div>
            </div>
            <!-- Expanded panel -->
            <div class="ks-select-item-wrap">
                <div class="ks-btn-state__label">Expanded</div>
                <div class="ks-select-trigger ks-select-trigger--open">
                    <span class="ks-select-trigger__text">Dropdown</span>
                    <svg class="ks-select-trigger__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 14l5-5 5 5z"/></svg>
                </div>
                <div class="ks-select-panel">
                    <div class="ks-select-opt">Option 1</div>
                    <div class="ks-select-opt ks-select-opt--hover">Option 2</div>
                    <div class="ks-select-opt ks-select-opt--selected">Option 3</div>
                    <div class="ks-select-opt">Option 4</div>
                </div>
            </div>
        </div>

        <!-- Group 2: Mit Icon -->
        <h3 class="ks-label">Mit Icon</h3>
        <div class="ks-select-group">
            <div class="ks-select-item-wrap">
                <div class="ks-btn-state__label">Expanded</div>
                <div class="ks-select-trigger ks-select-trigger--open">
                    <span class="ks-select-trigger__text">Dropdown</span>
                    <svg class="ks-select-trigger__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 14l5-5 5 5z"/></svg>
                </div>
                <div class="ks-select-panel">
                    <div class="ks-select-opt ks-select-opt--icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 1
                    </div>
                    <div class="ks-select-opt ks-select-opt--icon ks-select-opt--hover">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 2
                    </div>
                    <div class="ks-select-opt ks-select-opt--icon ks-select-opt--selected">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 3
                    </div>
                    <div class="ks-select-opt ks-select-opt--icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 4
                    </div>
                </div>
            </div>
        </div>

        <!-- Group 3: Mit Checkbox -->
        <h3 class="ks-label">Mit Checkbox</h3>
        <div class="ks-select-group">
            <div class="ks-select-item-wrap">
                <div class="ks-btn-state__label">Expanded</div>
                <div class="ks-select-trigger ks-select-trigger--open">
                    <span class="ks-select-trigger__text">Dropdown</span>
                    <svg class="ks-select-trigger__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 14l5-5 5 5z"/></svg>
                </div>
                <div class="ks-select-panel">
                    <div class="ks-select-opt ks-select-opt--check">
                        <input type="checkbox" tabindex="-1" style="pointer-events:none">
                        <span>Option 1</span>
                    </div>
                    <div class="ks-select-opt ks-select-opt--check ks-select-opt--hover">
                        <input type="checkbox" tabindex="-1" style="pointer-events:none">
                        <span>Option 2</span>
                    </div>
                    <div class="ks-select-opt ks-select-opt--check ks-select-opt--selected">
                        <input type="checkbox" checked tabindex="-1" style="pointer-events:none">
                        <span>Option 3</span>
                    </div>
                    <div class="ks-select-opt ks-select-opt--check">
                        <input type="checkbox" tabindex="-1" style="pointer-events:none">
                        <span>Option 4</span>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /select-einsp -->

    <!-- Tab 2: Dropdown mehrspaltig -->
    <div class="ks-tab-panel" data-panel="select-mehrsp">
        <div class="ks-select-group">
            <!-- Mit Icon, 2 columns -->
            <div class="ks-select-item-wrap">
                <div class="ks-btn-state__label">Mit Icon</div>
                <div class="ks-select-trigger ks-select-trigger--open">
                    <span class="ks-select-trigger__text">Dropdown</span>
                    <svg class="ks-select-trigger__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 14l5-5 5 5z"/></svg>
                </div>
                <div class="ks-select-panel ks-select-panel--cols2">
                    <div class="ks-select-opt ks-select-opt--icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 1
                    </div>
                    <div class="ks-select-opt ks-select-opt--icon ks-select-opt--hover">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 2
                    </div>
                    <div class="ks-select-opt ks-select-opt--icon ks-select-opt--selected">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 3
                    </div>
                    <div class="ks-select-opt ks-select-opt--icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 4
                    </div>
                    <div class="ks-select-opt ks-select-opt--icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 5
                    </div>
                    <div class="ks-select-opt ks-select-opt--icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                        Option 6
                    </div>
                </div>
            </div>

            <!-- Text only, 2 columns -->
            <div class="ks-select-item-wrap">
                <div class="ks-btn-state__label">Text only</div>
                <div class="ks-select-trigger ks-select-trigger--open">
                    <span class="ks-select-trigger__text">Dropdown</span>
                    <svg class="ks-select-trigger__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 14l5-5 5 5z"/></svg>
                </div>
                <div class="ks-select-panel ks-select-panel--cols2">
                    <div class="ks-select-opt">Option 1</div>
                    <div class="ks-select-opt ks-select-opt--hover">Option 2</div>
                    <div class="ks-select-opt ks-select-opt--selected">Option 3</div>
                    <div class="ks-select-opt">Option 4</div>
                    <div class="ks-select-opt">Option 5</div>
                    <div class="ks-select-opt">Option 6</div>
                </div>
            </div>
        </div>
    </div><!-- /select-mehrsp -->

    <!-- Tab 3: List Items -->
    <div class="ks-tab-panel" data-panel="select-items">

        <!-- Text only items -->
        <h3 class="ks-label">Text only</h3>
        <div class="ks-select-items-row">
            <div class="ks-select-opt ks-select-item-standalone">Option</div>
            <div class="ks-select-opt ks-select-item-standalone ks-select-item-standalone--overflow">Sehr langer Optionstext der nicht passt</div>
            <div class="ks-select-opt ks-select-opt--hover ks-select-item-standalone">Option</div>
            <div class="ks-select-opt ks-select-opt--selected ks-select-item-standalone">Option</div>
        </div>

        <!-- Mit Icon items -->
        <h3 class="ks-label">Mit Icon</h3>
        <div class="ks-select-items-row">
            <div class="ks-select-opt ks-select-opt--icon ks-select-item-standalone">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                Option
            </div>
            <div class="ks-select-opt ks-select-opt--icon ks-select-item-standalone ks-select-item-standalone--overflow">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                Sehr langer Optionstext der nicht passt
            </div>
            <div class="ks-select-opt ks-select-opt--icon ks-select-opt--hover ks-select-item-standalone">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                Option
            </div>
            <div class="ks-select-opt ks-select-opt--icon ks-select-opt--selected ks-select-item-standalone">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/></svg>
                Option
            </div>
        </div>

        <!-- Mit Checkbox items -->
        <h3 class="ks-label">Mit Checkbox</h3>
        <div class="ks-select-items-row">
            <div class="ks-select-opt ks-select-opt--check ks-select-item-standalone">
                <input type="checkbox" tabindex="-1" style="pointer-events:none">
                <span>Option</span>
            </div>
            <div class="ks-select-opt ks-select-opt--check ks-select-item-standalone ks-select-item-standalone--overflow">
                <input type="checkbox" tabindex="-1" style="pointer-events:none">
                <span>Sehr langer Optionstext der nicht passt</span>
            </div>
            <div class="ks-select-opt ks-select-opt--check ks-select-opt--hover ks-select-item-standalone">
                <input type="checkbox" tabindex="-1" style="pointer-events:none">
                <span>Option</span>
            </div>
            <div class="ks-select-opt ks-select-opt--check ks-select-opt--selected ks-select-item-standalone">
                <input type="checkbox" checked tabindex="-1" style="pointer-events:none">
                <span>Option</span>
            </div>
        </div>

    </div><!-- /select-items -->

</section>
