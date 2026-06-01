# STYLESHEET DIFFERENCE REPORT

- **Production style.css hash**: `e6de26a92cf5e299a6639c33d67da0e5` (lines: 3127)

- **Local style.css hash**: `afd879d2936c999d3408fdf0d6ce33dc` (lines: 3142)

## Line Diff (Production vs Local)
```diff
--- production_style.css
+++ local_style.css
@@ -395,7 +395,7 @@
 
 .lightbulb-img {
   max-height: 160px;
-  width: 85%;
+  width: 75%;
   object-fit: contain;
 }
 
@@ -558,6 +558,7 @@
 input[type="range"] {
   flex: 1;
   -webkit-appearance: none;
+  appearance: none;
   height: 8px;
   background: #E2E8F0;
   border-radius: 4px;
@@ -566,6 +567,7 @@
 
 input[type="range"]::-webkit-slider-thumb {
   -webkit-appearance: none;
+  appearance: none;
   width: 28px;
   height: 28px;
   background: var(--color-accent);
@@ -582,6 +584,12 @@
   color: var(--color-text-dark);
   min-width: 65px;
   text-align: end;
+}
+
+.calc-form {
+  min-width: 0;
+  width: 100%;
+  max-width: 100%;
 }
 
 /* Dropdowns Forms */
@@ -617,10 +625,30 @@
    ========================================================================== */
 
 #appliance-inputs-container {
-  max-height: none;
-  overflow-y: visible;
-  padding-right: 0;
+  max-height: 520px;
+  overflow-y: auto;
+  padding-right: 0.5rem;
   margin-bottom: 1.25rem;
+  width: 100%;
+  max-width: 100%;
+  min-width: 0;
+  box-sizing: border-box;
+  scrollbar-width: thin;
+  scrollbar-color: rgba(58, 141, 204, 0.25) rgba(0, 0, 0, 0.02);
+}
+
+#appliance-inputs-container::-webkit-scrollbar {
+  width: 5px;
+}
+#appliance-inputs-container::-webkit-scrollbar-track {
+  background: rgba(0, 0, 0, 0.02);
+}
+#appliance-inputs-container::-webkit-scrollbar-thumb {
+  background: rgba(58, 141, 204, 0.2);
+  border-radius: 10px;
+}
+#appliance-inputs-container::-webkit-scrollbar-thumb:hover {
+  background: var(--color-primary);
 }
 
 .appliance-filter-bar {
@@ -633,6 +661,32 @@
   overflow-x: auto;
   scrollbar-width: none;
   border: 1px solid rgba(0, 0, 0, 0.03);
+  width: 100%;
+  max-width: 100%;
+  min-width: 0;
+  box-sizing: border-box;
+}
+
+/* Info Banner for Appliances */
+.appliance-info-banner {
+  margin-bottom: 1.25rem;
+  padding: 0.8rem 1rem;
+  background: rgba(58, 141, 204, 0.08);
+  border-left: 4px solid var(--color-primary);
+  border-radius: 8px;
+  font-size: 0.85rem;
+  color: #1e4b6e;
+  display: flex;
+  align-items: center;
+  gap: 8px;
+  width: 100%;
+  max-width: 100%;
+  box-sizing: border-box;
+}
+
+html[dir="rtl"] .appliance-info-banner {
+  border-left: none;
+  border-right: 4px solid var(--color-primary);
 }
 
 .appliance-filter-bar::-webkit-scrollbar {
@@ -670,6 +724,10 @@
   flex-direction: column;
   gap: 0.5rem;
   margin-bottom: 1rem;
+  width: 100%;
+  max-width: 100%;
+  min-width: 0;
+  box-sizing: border-box;
 }
 
 /* Extremely Compact horizontal row layout for mobiles */
@@ -1132,8 +1190,8 @@
    ========================================================================== */
 
 .datasheet-grid {
-  display: flex;
-  flex-direction: column;
+  display: grid;
+  grid-template-columns: repeat(2, 1fr);
   gap: 1.25rem;
   width: 100%;
 }
@@ -2126,13 +2184,7 @@
   }
 
   /* Datasheets */
-  .datasheet-grid,
-  .expandable-brand-grid.expanded {
-    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
-    display: grid;
-    gap: 1.5rem;
-    align-items: stretch;
-  }
+  .datasheet-grid, 
   .datasheet-card.brand-card {
     height: 100%;
   }
@@ -2214,6 +2266,9 @@
    ========================================================================== */
 
 @media (min-width: 1024px) {
+  .datasheet-grid {
+    grid-template-columns: repeat(4, 1fr);

... [Truncated 105 lines of diff]
```