with open('theme-v3.css', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

# Let's locate the duplicate CTA banner section at the bottom.
# We know it starts near the end of the file.
dup_start_header = "/* ==========================================================================\n   11. CTA Banner Section - Stacked Mobile\n   ========================================================================== */"
rtl_header = "/* ==========================================================================\n   14. RTL Overrides for Arabic Language Context"

start_idx = content.rfind(dup_start_header)
end_idx = content.find(rtl_header)

print("Duplicate start index:", start_idx)
print("RTL header index:", end_idx)

if start_idx != -1 and end_idx != -1 and start_idx < end_idx:
    # Remove the duplicate content
    new_content = content[:start_idx] + content[end_idx:]
    with open('theme-v3.css', 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Successfully removed duplicate CTA, Gallery, and Why Choose Us styles from the end of theme-v3.css!")
else:
    print("Failed to locate target indices correctly!")
