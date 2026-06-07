CSS_PATH = 'style.css'

with open(CSS_PATH, 'r', encoding='utf-8') as f:
    content = f.read()

print("Deduplicating style.css...")

# Unique styles to preserve (which were in Block 1 & 2 but missing in Block 4)
preserved_widgets_css = """
/* ── Preserved Unique Widget Styles (Consolidated) ── */
.discovery-dashboard h3 {
  font-size: 1.25rem;
  font-weight: 900;
  color: var(--color-text-dark);
  padding-bottom: 0.65rem;
  border-bottom: 2px solid #F1F5F9;
  margin-bottom: 1.25rem;
}
.section-title-discovery {
  font-size: var(--fs-section-title);
  font-weight: 800;
  color: var(--color-text-dark);
  margin: 0;
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding-bottom: var(--space-2);
  border-bottom: 1px solid rgba(0,0,0,0.05);
}
.section-title-discovery span {
  font-size: 1.25rem;
}
.insights-grid-sub {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-3);
}
.select-tech-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr) !important;
  gap: var(--space-3) !important;
}
#db-card-install-cost {
  grid-column: span 2 !important;
}
.gamification-section {
  padding-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}
.game-score-card {
  background: var(--color-bg);
  border-radius: 1rem;
  padding: var(--space-3);
  border: 1px solid rgba(0,0,0,0.03);
  width: 100%;
}
.game-score-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 700;
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin-bottom: var(--space-2);
}
.score-value-pill {
  background: var(--color-primary);
  color: #ffffff;
  padding: 0.2rem 0.65rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 800;
}
.progress-bar-wrapper {
  background: #E2E8F0;
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}
.progress-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.progress-bar-fill.fill-energy {
  background: var(--color-primary);
}
.green-impact-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}
.green-metrics {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-3);
  text-align: center;
}
.green-sub {
  display: flex;
  flex-direction: column;
}
.green-sub span {
  font-size: 2rem;
  font-weight: 900;
  color: #059669;
  line-height: 1.1;
}
.green-sub small {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--color-text-muted);
  margin-top: var(--space-1);
  line-height: 1.3;
}
.animated-green-bar {
  background: #E2E8F0;
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}
.green-leaf-progress {
  height: 100%;
  background: linear-gradient(to right, #059669, #10b981);
  border-radius: 4px;
  transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.suitability-score-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}
.suitability-score-card .game-score-header strong {
  font-size: 1.65rem;
  font-weight: 900;
  color: #3eb649;
  line-height: 1;
}
.suitability-badges {
  display: flex;
  justify-content: space-between;
  gap: var(--space-1);
}
.suit-badge {
  flex: 1;
  text-align: center;
  padding: 0.4rem 0;
  border-radius: 0.5rem;
  font-size: 0.82rem;
  font-weight: 800;
  background: #E2E8F0;
  color: #94A3B8;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.suit-badge.active[data-score="C"] { background: #EF4444; color: white; }
.suit-badge.active[data-score="B"] { background: #F59E0B; color: white; }
.suit-badge.active[data-score="A"] { background: #10B981; color: white; }
.suit-badge.active[data-score="A+"] { background: #059669; color: white; }
"""

# Let's find Block 1 by matching the first occurrence of '.discovery-dashboard {'
first_dashboard_idx = content.find('.discovery-dashboard {')
print(f"First dashboard index: {first_dashboard_idx}")

# And Block 1 ends with '.progress-bar-fill.fill-energy { ... }'
# Let's locate the first '.progress-bar-fill.fill-energy {' block
fill_energy_str = '.progress-bar-fill.fill-energy {\n  background: var(--color-primary);\n}'
fill_energy_idx = content.find(fill_energy_str, first_dashboard_idx)
print(f"Fill energy index: {fill_energy_idx}")

if first_dashboard_idx != -1 and fill_energy_idx != -1:
    end_of_block1 = fill_energy_idx + len(fill_energy_str)
    block1_to_replace = content[first_dashboard_idx:end_of_block1]
    content = content.replace(block1_to_replace, preserved_widgets_css)
    print("Block 1 successfully replaced with consolidated widget styles.")
else:
    print("Could not find Block 1 coordinates!")

# Now find Block 2 by matching '.discovery-dashboard {' after the replacement
# The first dashboard index will now be the one at line 4435 (shifted)
second_dashboard_idx = content.find('.discovery-dashboard {', first_dashboard_idx + len(preserved_widgets_css))
print(f"Second dashboard index: {second_dashboard_idx}")

# Block 2 ends with '.suit-badge.active[data-score="A+"] { background: #059669; color: white; }'
suit_badge_aplus_str = '.suit-badge.active[data-score="A+"] { background: #059669; color: white; }'
suit_badge_idx = content.find(suit_badge_aplus_str, second_dashboard_idx)
print(f"Suit badge index: {suit_badge_idx}")

if second_dashboard_idx != -1 and suit_badge_idx != -1:
    end_of_block2 = suit_badge_idx + len(suit_badge_aplus_str)
    block2_to_replace = content[second_dashboard_idx:end_of_block2]
    content = content.replace(block2_to_replace, "/* Older duplicate block 2 removed */")
    print("Block 2 successfully replaced with a comment.")
else:
    print("Could not find Block 2 coordinates!")

# Update the grid insights-row override in Block 4
# Block 4's insights-row media queries:
insights_row_override = """.discovery-dashboard .insights-row { display: grid !important; gap: 1.25rem !important; width: 100% !important; grid-template-columns: 1fr !important; }
@media (min-width: 768px) { .discovery-dashboard .insights-row { grid-template-columns: repeat(2, 1fr) !important; } }
@media (min-width: 1024px) { .discovery-dashboard .insights-row { grid-template-columns: repeat(3, 1fr) !important; } }"""

override_idx = content.find(insights_row_override)
print(f"Insights row override index: {override_idx}")

if override_idx != -1:
    clean_override = """.discovery-dashboard .insights-row { 
    display: grid !important; 
    gap: var(--space-4) !important; 
    width: 100% !important; 
    grid-template-columns: 1fr !important; 
    align-items: flex-start !important;
}

@media (min-width: 768px) { 
    .discovery-dashboard .insights-row { 
        grid-template-columns: 1fr 1fr !important; 
        grid-template-rows: auto auto !important;
    } 
    .col-insights:nth-child(1) {
        grid-column: 1 !important;
        grid-row: 1 !important;
    }
    .col-insights:nth-child(2) {
        grid-column: 1 !important;
        grid-row: 2 !important;
    }
    .col-insights:nth-child(3) {
        grid-column: 2 !important;
        grid-row: 1 / span 2 !important;
    }
}"""
    content = content.replace(insights_row_override, clean_override)
    print("Insights row override successfully updated to stacked 2-column grid.")
else:
    print("Could not find insights row override block!")

# Add custom select style and progress ring wrapper svg fix
additional_styles = """
/* ── Custom Select Arrow Styles ── */
select.manual-bill-input,
.wizard-footer-controls select {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'></polyline></svg>") !important;
    background-repeat: no-repeat !important;
    background-position: right 1rem center !important;
    background-size: 1.2em !important;
    padding-right: 2.5rem !important;
}
html[dir="rtl"] select.manual-bill-input,
html[dir="rtl"] .wizard-footer-controls select {
    background-position: left 1rem center !important;
    padding-right: 1.25rem !important;
    padding-left: 2.5rem !important;
}

/* ── Progress Ring Responsive Scaling Fix ── */
.calibration-ring-section .progress-ring-svg {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    transform: rotate(-90deg) !important;
    width: 100% !important;
    height: 100% !important;
}
"""

content += additional_styles
print("Additional styles successfully appended.")

with open(CSS_PATH, 'w', encoding='utf-8') as f:
    f.write(content)

print("Deduplication and stabilization complete!")
